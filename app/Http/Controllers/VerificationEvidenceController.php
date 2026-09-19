<?php

namespace App\Http\Controllers;

use App\Models\VerificationEvidence;
use App\Models\JournalMembershipApplication;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class VerificationEvidenceController extends Controller
{
    /**
     * Upload evidence for an application.
     */
    public function store(Request $request, JournalMembershipApplication $application)
    {
        $user = Auth::user();

        if ($application->user_id !== $user->id) {
            abort(403, 'Unauthorized access.');
        }

        if (!in_array($application->status, [JournalMembershipApplication::STATUS_DRAFT, JournalMembershipApplication::STATUS_NEEDS_REVISION])) {
            return back()->with('error', 'Cannot upload evidence in current status.');
        }

        $validated = $request->validate([
            'category' => 'required|in:' . implode(',', [
                VerificationEvidence::CATEGORY_IDENTITY,
                VerificationEvidence::CATEGORY_ACADEMIC_CREDENTIAL,
                VerificationEvidence::CATEGORY_INSTITUTION_AFFILIATION,
                VerificationEvidence::CATEGORY_RESEARCHER_IDENTITY,
                VerificationEvidence::CATEGORY_SUPPORTING_DOCUMENT,
            ]),
            'document' => 'required|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:10240', // 10MB max, safe types
        ]);

        $file = $request->file('document');
        
        // Generate safe random name
        $filename = Str::random(40) . '.' . $file->getClientOriginalExtension();
        
        // Store in private disk
        $path = $file->storeAs('private/verifications', $filename, 'local');

        VerificationEvidence::create([
            'user_id' => $user->id,
            'journal_membership_application_id' => $application->id,
            'category' => $validated['category'],
            'file_path' => $path,
            'status' => VerificationEvidence::STATUS_PENDING,
        ]);

        return back()->with('success', 'Document uploaded successfully.');
    }

    /**
     * Download an evidence document.
     */
    public function download(VerificationEvidence $evidence)
    {
        $user = Auth::user();

        // Allow if user is owner
        $isOwner = $evidence->user_id === $user->id;
        
        // Or if user is platform admin (verifier)
        $isAdmin = $user->is_admin || $user->hasRole('admin');

        if (!$isOwner && !$isAdmin) {
            abort(403, 'Unauthorized access.');
        }

        if (!Storage::disk('local')->exists($evidence->file_path)) {
            abort(404, 'File not found.');
        }

        return Storage::disk('local')->download($evidence->file_path);
    }
}
