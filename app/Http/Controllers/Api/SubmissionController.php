<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SubmissionFileStoreRequest;
use App\Http\Requests\SubmissionStoreRequest;
use App\Http\Requests\SubmissionUpdateRequest;
use App\Http\Resources\SubmissionResource;
use App\Models\Submission;
use App\Repositories\SubmissionRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SubmissionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $submissions = SubmissionRepository::getScopedQuery(auth()->user())
            ->orderBy('id', 'desc')
            ->paginate($request->per_page ?? 15);

        return SubmissionResource::collection($submissions);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(SubmissionStoreRequest $request)
    {
        $this->authorize('create', Submission::class);

        $submission = Submission::forceCreate([
            'journal_id' => $request->journal_id,
            'created_by' => auth()->id(),
            'title' => $request->title,
            'abstract' => $request->abstract,
            'keywords' => $request->keywords,
            'status' => 'draft',
        ]);

        if ($request->has('authors') && is_array($request->authors)) {
            SubmissionRepository::syncAuthors($submission, $request->authors);
        }

        $submission->load(['authors', 'revisions.files']);

        return $this->json('Submission created successfully.', new SubmissionResource($submission), 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Submission $submission)
    {
        $this->authorize('view', $submission);

        $submission->load(['authors', 'revisions.files', 'journal']);

        return $this->json('Submission retrieved successfully.', new SubmissionResource($submission));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(SubmissionUpdateRequest $request, Submission $submission)
    {
        $this->authorize('update', $submission);

        $submission->forceFill([
            'title' => $request->title ?? $submission->title,
            'abstract' => $request->has('abstract') ? $request->abstract : $submission->abstract,
            'keywords' => $request->has('keywords') ? $request->keywords : $submission->keywords,
        ])->save();

        if ($request->has('authors') && is_array($request->authors)) {
            SubmissionRepository::syncAuthors($submission, $request->authors);
        }

        $submission->load(['authors', 'revisions.files']);

        return $this->json('Submission updated successfully.', new SubmissionResource($submission));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Submission $submission)
    {
        $this->authorize('delete', $submission);

        $submission->delete(); // Soft delete

        return $this->json('Submission deleted successfully.', null, 204);
    }

    /**
     * Transition the submission from draft to submitted.
     */
    public function submit(Submission $submission)
    {
        $this->authorize('submit', $submission);

        // Validation for submission transition
        $journal = $submission->journal;
        if (!$journal || $journal->status !== 'active') {
            return $this->json('The target journal is not active.', null, 422);
        }

        if ($submission->authors()->count() === 0) {
            return $this->json('A submission must have at least one author.', null, 422);
        }

        $correspondingAuthorsCount = $submission->authors()->where('is_corresponding', true)->count();
        if ($correspondingAuthorsCount !== 1) {
            return $this->json('A submission must have exactly one corresponding author.', null, 422);
        }

        if ($submission->files()->count() === 0) {
            return $this->json('A submission must have at least one manuscript file.', null, 422);
        }

        $submission->forceFill([
            'status' => 'submitted',
            'submitted_at' => now(),
        ])->save();

        $submission->load(['authors', 'revisions.files']);

        return $this->json('Submission has been submitted successfully.', new SubmissionResource($submission));
    }

    /**
     * Transition the submission from revision_required to revision_submitted.
     */
    public function submitRevision(Submission $submission)
    {
        $this->authorize('submitRevision', $submission);

        if ($submission->authors()->count() === 0) {
            return $this->json('A submission must have at least one author.', null, 422);
        }

        $correspondingAuthorsCount = $submission->authors()->where('is_corresponding', true)->count();
        if ($correspondingAuthorsCount !== 1) {
            return $this->json('A submission must have exactly one corresponding author.', null, 422);
        }

        if ($submission->files()->count() === 0) {
            return $this->json('A submission must have at least one manuscript file.', null, 422);
        }

        $latestRevision = $submission->revisions()->orderBy('version_number', 'desc')->first();
        if ($latestRevision && $latestRevision->reviewRounds()->whereHas('assignments')->exists()) {
            return $this->json('You must upload a new manuscript file before submitting the revision.', null, 422);
        }

        // Must run in transaction to log audit event
        \Illuminate\Support\Facades\DB::transaction(function () use ($submission) {
            $submission->forceFill([
                'status' => 'revision_submitted',
            ])->save();
            
            $submission->editorialEvents()->create([
                'user_id' => auth()->id(),
                'action' => \App\Models\SubmissionEditorialEvent::ACTION_REVISION_SUBMITTED,
            ]);
        });

        $submission->load(['authors', 'revisions.files']);

        return $this->json('Revision has been submitted successfully.', new SubmissionResource($submission));
    }

    /**
     * Upload a manuscript file for the submission.
     */
    public function uploadFile(SubmissionFileStoreRequest $request, Submission $submission)
    {
        $this->authorize('uploadFile', $submission);

        $file = $request->file('file');
        
        $revision = $submission->revisions()->orderBy('version_number', 'desc')->first();
        if (!$revision) {
            $revision = $submission->revisions()->create(['version_number' => 1]);
        } else if ($revision->reviewRounds()->whereHas('assignments')->exists()) {
            // The latest revision has entered review. We must create a new one to maintain immutability.
            $revision = $submission->revisions()->create(['version_number' => $revision->version_number + 1]);
        }

        // Only remove existing files on the *current unlocked* revision
        foreach ($revision->files as $existingFile) {
            if (Storage::disk($existingFile->disk)->exists($existingFile->file_path)) {
                Storage::disk($existingFile->disk)->delete($existingFile->file_path);
            }
            $existingFile->delete();
        }

        $disk = 'local';
        $filename = Str::uuid() . '.' . $file->extension();
        $path = 'submissions/' . $submission->id . '/' . $filename;

        // Store privately on the local disk
        Storage::disk($disk)->put($path, file_get_contents($file));

        $submissionFile = $revision->files()->create([
            'disk' => $disk,
            'file_path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
        ]);

        $submission->load(['authors', 'revisions.files']);

        return $this->json('File uploaded successfully.', new SubmissionResource($submission));
    }

    /**
     * Securely download a manuscript file.
     */
    public function downloadFile(Submission $submission, $fileId)
    {
        $this->authorize('downloadFile', $submission);

        $file = $submission->files()->findOrFail($fileId);

        if (!Storage::disk($file->disk)->exists($file->file_path)) {
            return response()->json(['message' => 'File not found.'], 404);
        }

        return Storage::disk($file->disk)->download($file->file_path, $file->original_name);
    }
}
