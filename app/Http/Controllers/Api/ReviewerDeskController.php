<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PeerReview;
use App\Models\ReviewAssignment;
use App\Models\SubmissionEditorialEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

class ReviewerDeskController extends Controller
{
    /**
     * Get reviewer's assignments.
     */
    public function index(Request $request)
    {
        $assignments = ReviewAssignment::with(['reviewRound.submissionRevision.submission.journal'])
            ->where('reviewer_id', auth()->id())
            ->orderBy('id', 'desc')
            ->paginate($request->per_page ?? 15);

        return \App\Http\Resources\ReviewerAssignmentResource::collection($assignments);
    }

    /**
     * Show a specific assignment.
     */
    public function show(ReviewAssignment $assignment)
    {
        Gate::authorize('view', $assignment);

        $assignment->load(['reviewRound.submissionRevision.submission.journal', 'reviewRound.submissionRevision.files', 'peerReview.criterionResponses']);

        if ($assignment->review_mode === 'open') {
            $assignment->reviewRound->submissionRevision->submission->load('authors');
        }

        return new \App\Http\Resources\ReviewerAssignmentResource($assignment);
    }

    /**
     * Accept a review assignment.
     */
    public function accept(ReviewAssignment $assignment)
    {
        Gate::authorize('update', $assignment);

        if ($assignment->status !== 'assigned') {
            return $this->json('Assignment cannot be accepted in its current state.', null, 422);
        }

        DB::transaction(function () use ($assignment) {
            $assignment->update([
                'status' => 'accepted',
                'accepted_at' => now(),
            ]);

            $assignment->reviewRound->submissionRevision->submission->editorialEvents()->create([
                'user_id' => auth()->id(),
                'action' => SubmissionEditorialEvent::ACTION_REVIEW_INVITATION_ACCEPTED,
                'payload' => ['assignment_id' => $assignment->id],
            ]);
        });

        return $this->json('Assignment accepted.');
    }

    /**
     * Decline a review assignment.
     */
    public function decline(ReviewAssignment $assignment)
    {
        Gate::authorize('update', $assignment);

        if ($assignment->status !== 'assigned') {
            return $this->json('Assignment cannot be declined in its current state.', null, 422);
        }

        DB::transaction(function () use ($assignment) {
            $assignment->update([
                'status' => 'declined',
                'declined_at' => now(),
            ]);

            $assignment->reviewRound->submissionRevision->submission->editorialEvents()->create([
                'user_id' => auth()->id(),
                'action' => SubmissionEditorialEvent::ACTION_REVIEW_INVITATION_DECLINED,
                'payload' => ['assignment_id' => $assignment->id],
            ]);
        });

        return $this->json('Assignment declined.');
    }

    /**
     * Submit the peer review.
     */
    public function submitReview(Request $request, ReviewAssignment $assignment)
    {
        Gate::authorize('update', $assignment);

        if (!in_array($assignment->status, ['accepted', 'in_progress'])) {
            return $this->json('Assignment must be accepted before submitting a review.', null, 422);
        }

        $request->validate([
            'recommendation' => 'required|string',
            'comments_to_editor' => 'nullable|string',
            'comments_to_author' => 'nullable|string',
            'criteria_responses' => 'nullable|array',
            'criteria_responses.*.criterion_id' => 'required|exists:review_criteria,id',
            'criteria_responses.*.response' => 'required',
        ]);

        DB::transaction(function () use ($request, $assignment) {
            $peerReview = PeerReview::updateOrCreate(
                ['review_assignment_id' => $assignment->id],
                [
                    'recommendation' => $request->recommendation,
                    'comments_to_editor' => $request->comments_to_editor,
                    'comments_to_author' => $request->comments_to_author,
                    'submitted_at' => now(),
                ]
            );

            if ($request->has('criteria_responses')) {
                foreach ($request->criteria_responses as $cr) {
                    $peerReview->criterionResponses()->create([
                        'review_criterion_id' => $cr['criterion_id'],
                        'response' => is_array($cr['response']) ? json_encode($cr['response']) : $cr['response'],
                    ]);
                    
                    // Lock the criterion by keeping its status as 'active' but relying on the backend to enforce immutability once linked.
                }
            }

            $assignment->update([
                'status' => 'submitted',
            ]);

            $assignment->reviewRound->submissionRevision->submission->editorialEvents()->create([
                'user_id' => auth()->id(),
                'action' => SubmissionEditorialEvent::ACTION_REVIEW_SUBMITTED,
                'payload' => ['assignment_id' => $assignment->id, 'peer_review_id' => $peerReview->id],
            ]);
        });

        return $this->json('Review submitted successfully.');
    }

    /**
     * Get active review criteria for the journal of this assignment.
     * Used by the reviewer to know what criteria to evaluate.
     */
    public function getCriteria(ReviewAssignment $assignment)
    {
        Gate::authorize('view', $assignment);

        $journalId = $assignment->reviewRound->submissionRevision->submission->journal_id;

        $criteria = \App\Models\ReviewCriterion::where('journal_id', $journalId)
            ->where('status', 'active')
            ->orderBy('id')
            ->get(['id', 'name', 'type', 'config']);

        return response()->json([
            'message' => 'Review criteria retrieved successfully.',
            'data'    => $criteria,
        ]);
    }

    /**
     * Download a manuscript file securely.
     */
    public function downloadFile(ReviewAssignment $assignment, \App\Models\SubmissionFile $file)
    {
        Gate::authorize('view', $assignment);

        if ($file->submission_revision_id !== $assignment->reviewRound->submission_revision_id) {
            return response()->json(['message' => 'File does not belong to this submission.'], 403);
        }

        if (!in_array($assignment->status, ['accepted', 'in_progress', 'submitted'])) {
            return response()->json(['message' => 'You must accept the assignment before downloading files.'], 403);
        }

        if (!Storage::disk('local')->exists($file->file_path)) {
            return response()->json(['message' => 'File not found on server.'], 404);
        }

        return Storage::disk('local')->download($file->file_path, $file->original_name);
    }
}
