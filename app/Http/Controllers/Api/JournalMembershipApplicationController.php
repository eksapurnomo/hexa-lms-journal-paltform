<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Journal;
use App\Models\JournalMembershipApplication;
use App\Models\JournalMembership;
use App\Models\JournalReviewerCapability;
use App\Services\JournalMembershipApplicationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class JournalMembershipApplicationController extends Controller
{
    protected JournalMembershipApplicationService $service;

    public function __construct(JournalMembershipApplicationService $service)
    {
        $this->service = $service;
    }

    protected function resolveJournal($journalIdentifier): Journal
    {
        // Journal identification by slug or ID based on the project's existing convention
        return Journal::where('status', 'active')
            ->where(function ($query) use ($journalIdentifier) {
                $query->where('id', $journalIdentifier)
                      ->orWhere('slug', $journalIdentifier);
            })->firstOrFail();
    }

    public function show($journalIdentifier)
    {
        $user = Auth::guard('api')->user();
        $journal = $this->resolveJournal($journalIdentifier);

        $application = JournalMembershipApplication::where('user_id', $user->id)
            ->where('journal_id', $journal->id)
            ->first();

        if (!$application) {
            return $this->json('Application not found', [], 404);
        }

        return $this->json('Application found', [
            'application' => $application->makeHidden(['reviewed_by', 'reviewer_note', 'created_at', 'updated_at'])
        ]);
    }

    public function store(Request $request, $journalIdentifier)
    {
        $user = Auth::guard('api')->user();
        $journal = $this->resolveJournal($journalIdentifier);

        $validated = $request->validate([
            'requested_role' => 'required|in:owner,editor,reviewer',
            'academic_type' => 'required|in:lecturer,researcher',
            'highest_degree' => 'nullable|string|max:255',
            'academic_position' => 'nullable|string|max:255',
            'institution_type' => 'nullable|string|max:255|in:university,research_institute,government_research,ngo,private_research,think_tank,other,independent',
            'institution_id' => 'nullable|exists:institutions,id',
            'institution' => 'nullable|string|max:255',
            'department' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:255',
            'biography' => 'nullable|string',
            'research_interests' => 'nullable|string',
            'institutional_email' => 'nullable|email|max:255',
            'orcid' => 'nullable|string|max:255',
            'sinta_id' => 'nullable|string|max:255',
            'scopus_author_id' => 'nullable|string|max:255',
            'google_scholar_url' => 'nullable|url|max:255',
            'recruitment_source' => 'nullable|string|max:255',
            'declarations' => 'nullable|array',
        ]);

        $validated['journal_id'] = $journal->id;

        try {
            $app = $this->service->createDraft($user, $validated);

            return $this->json('Application drafted successfully', [
                'application' => $app->makeHidden(['reviewed_by', 'reviewer_note'])
            ], 201);
        } catch (\Exception $e) {
            return $this->json($e->getMessage(), [], 422);
        }
    }

    public function update(Request $request, $journalIdentifier)
    {
        $user = Auth::guard('api')->user();
        $journal = $this->resolveJournal($journalIdentifier);

        $application = JournalMembershipApplication::where('user_id', $user->id)
            ->where('journal_id', $journal->id)
            ->first();

        if (!$application) {
            return $this->json('Application not found', [], 404);
        }

        $validated = $request->validate([
            'academic_type' => 'required|in:lecturer,researcher',
            'highest_degree' => 'nullable|string|max:255',
            'academic_position' => 'nullable|string|max:255',
            'institution_type' => 'nullable|string|max:255|in:university,research_institute,government_research,ngo,private_research,think_tank,other,independent',
            'institution_id' => 'nullable|exists:institutions,id',
            'institution' => 'nullable|string|max:255',
            'department' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:255',
            'biography' => 'nullable|string',
            'research_interests' => 'nullable|string',
            'institutional_email' => 'nullable|email|max:255',
            'orcid' => 'nullable|string|max:255',
            'sinta_id' => 'nullable|string|max:255',
            'scopus_author_id' => 'nullable|string|max:255',
            'google_scholar_url' => 'nullable|url|max:255',
            'recruitment_source' => 'nullable|string|max:255',
            'declarations' => 'nullable|array',
        ]);

        try {
            $updatedApp = $this->service->updateDraft($user, $application, $validated);

            return $this->json('Application profile updated successfully', [
                'application' => $updatedApp->makeHidden(['reviewed_by', 'reviewer_note'])
            ], 200);
        } catch (\Exception $e) {
            return $this->json($e->getMessage(), [], 422);
        }
    }

    public function submit(Request $request, $journalIdentifier)
    {
        $user = Auth::guard('api')->user();
        $journal = $this->resolveJournal($journalIdentifier);

        $application = JournalMembershipApplication::where('user_id', $user->id)
            ->where('journal_id', $journal->id)
            ->first();

        if (!$application) {
            return $this->json('Application not found', [], 404);
        }

        try {
            $submittedApp = $this->service->submitApplication($user, $application);

            return $this->json('Application submitted successfully for review', [
                'application' => $submittedApp->makeHidden(['reviewed_by', 'reviewer_note'])
            ], 200);
        } catch (\Exception $e) {
            return $this->json($e->getMessage(), [], 422);
        }
    }

    public function status($journalIdentifier)
    {
        $user = Auth::guard('api')->user();
        $journal = $this->resolveJournal($journalIdentifier);

        $application = JournalMembershipApplication::where('user_id', $user->id)
            ->where('journal_id', $journal->id)
            ->first();

        if (!$application) {
            return $this->json('Application not found', [], 404);
        }

        $statusData = [
            'id' => $application->id,
            'journal_id' => $application->journal_id,
            'requested_role' => $application->requested_role,
            'status' => $application->status,
            'submitted_at' => $application->submitted_at,
            'reviewed_at' => $application->reviewed_at,
        ];

        // For rejected / needs_revision applications, expose the reason if available
        if (in_array($application->status, [
            JournalMembershipApplication::STATUS_REJECTED,
            JournalMembershipApplication::STATUS_NEEDS_REVISION
        ])) {
            $statusData['reviewer_note'] = $application->reviewer_note;
        }

        return $this->json('Application status retrieved', [
            'status' => $statusData
        ], 200);
    }

    public function membership($journalIdentifier)
    {
        $user = Auth::guard('api')->user();
        $journal = $this->resolveJournal($journalIdentifier);

        $membership = JournalMembership::where('user_id', $user->id)
            ->where('journal_id', $journal->id)
            ->first();

        if (!$membership) {
            return $this->json('Membership not found', [], 404);
        }

        return $this->json('Membership status retrieved', [
            'membership' => [
                'journal_id' => $membership->journal_id,
                'role' => $membership->role,
                'status' => $membership->status,
                'created_at' => $membership->created_at,
            ]
        ], 200);
    }

    public function reviewerCapability($journalIdentifier)
    {
        $user = Auth::guard('api')->user();
        $journal = $this->resolveJournal($journalIdentifier);

        $membership = JournalMembership::where('user_id', $user->id)
            ->where('journal_id', $journal->id)
            ->where('role', 'reviewer')
            ->where('status', 'active')
            ->first();

        if (!$membership) {
            return $this->json('Reviewer membership not found or not active', [], 404);
        }

        $capability = JournalReviewerCapability::where('journal_membership_id', $membership->id)->first();

        if (!$capability) {
            return $this->json('Reviewer capability not found', [], 404);
        }

        return $this->json('Reviewer capability retrieved', [
            'capability' => [
                'available_for_review' => $capability->available_for_review,
                'max_reviews_per_month' => $capability->max_reviews_per_month,
                'years_of_experience' => $capability->years_of_experience,
                'previous_experience' => $capability->previous_experience,
            ]
        ], 200);
    }
}
