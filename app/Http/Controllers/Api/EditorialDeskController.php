<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Submission;
use App\Models\SubmissionEditorialEvent;
use App\Models\User;
use App\Repositories\SubmissionRepository;
use App\Http\Resources\EditorialSubmissionResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class EditorialDeskController extends Controller
{
    public function index(Request $request)
    {
        $query = SubmissionRepository::getScopedQuery(auth()->user())
            ->where('status', '!=', Submission::STATUS_DRAFT); // Editors don't see drafts

        // Filter by Status
        if ($request->has('status') && !empty($request->status)) {
            $query->where('status', $request->status);
        }

        // Filter by Journal
        if ($request->has('journal_id') && !empty($request->journal_id)) {
            $query->where('journal_id', $request->journal_id);
        }

        // Search by Title or ID
        if ($request->has('search') && !empty($request->search)) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('title', 'like', '%' . $searchTerm . '%')
                  ->orWhere('id', 'like', '%' . $searchTerm . '%');
            });
        }

        // Sorting
        $sortField = $request->input('sort_by', 'id');
        $sortDirection = $request->input('sort_dir', 'desc');
        
        $allowedSorts = ['id', 'title', 'status', 'created_at', 'updated_at'];
        if (in_array($sortField, $allowedSorts) && in_array(strtolower($sortDirection), ['asc', 'desc'])) {
            $query->orderBy($sortField, $sortDirection);
        } else {
            $query->orderBy('id', 'desc');
        }

        $submissions = $query->paginate($request->per_page ?? 15);

        return EditorialSubmissionResource::collection($submissions);
    }

    /**
     * Display the specified submission with editorial details.
     */
    public function show(Submission $submission)
    {
        Gate::authorize('editorial-process', [$submission]);

        $submission->load(['authors', 'revisions.files', 'revisions.reviewRounds.assignments.reviewer', 'revisions.reviewRounds.assignments.peerReview', 'revisions.reviewRounds.editorialDecision', 'journal', 'editor', 'editorialEvents.user']);

        return $this->json('Editorial submission retrieved successfully.', new EditorialSubmissionResource($submission));
    }

    /**
     * Transition the submission status (editorial workflow actions).
     */
    public function updateStatus(Request $request, Submission $submission)
    {
        Gate::authorize('editorial-process', [$submission]);

        $request->validate([
            'status' => 'required|string',
        ]);

        $newStatus = $request->status;

        // Allowed editorial transitions per current status
        $allowedTransitions = [
            Submission::STATUS_SUBMITTED         => [Submission::STATUS_EDITORIAL_ASSESSMENT],
            Submission::STATUS_REVISION_SUBMITTED => [Submission::STATUS_EDITORIAL_ASSESSMENT],
            Submission::STATUS_EDITORIAL_ASSESSMENT => [
                Submission::STATUS_REVIEW_PENDING,
                Submission::STATUS_REVISION_REQUIRED,
                Submission::STATUS_REJECTED,
                Submission::STATUS_ACCEPTED,
            ],
            Submission::STATUS_REVIEW_PENDING => [
                Submission::STATUS_ACCEPTED,
                Submission::STATUS_REVISION_REQUIRED,
                Submission::STATUS_REJECTED,
            ],
        ];

        $currentStatus = $submission->status;
        $allowed = $allowedTransitions[$currentStatus] ?? [];

        if (!in_array($newStatus, $allowed)) {
            return $this->json(
                "Cannot transition from '{$currentStatus}' to '{$newStatus}'.",
                null, 422
            );
        }

        DB::transaction(function () use ($submission, $newStatus) {
            $submission->forceFill(['status' => $newStatus])->save();

            $submission->editorialEvents()->create([
                'user_id' => auth()->id(),
                'action'  => 'status_transition',
                'payload' => ['from' => $submission->getOriginal('status') ?? $newStatus, 'to' => $newStatus],
            ]);
        });

        $submission->load(['authors', 'revisions.files', 'revisions.reviewRounds.assignments.reviewer', 'revisions.reviewRounds.assignments.peerReview', 'revisions.reviewRounds.editorialDecision', 'journal', 'editor', 'editorialEvents.user']);

        return $this->json('Status updated successfully.', new EditorialSubmissionResource($submission));
    }

    /**
     * Get eligible editors for the submission's journal.
     */
    public function eligibleEditors(Submission $submission)
    {
        Gate::authorize('editorial-assign', [$submission]);

        $editors = User::whereHas('journalMemberships', function ($query) use ($submission) {
            $query->where('journal_id', $submission->journal_id)
                  ->where('role', 'editor')
                  ->where('status', 'active');
        })->select('id', 'name', 'email')->get();

        return response()->json([
            'message' => 'Eligible editors retrieved successfully.',
            'data' => $editors
        ]);
    }

    /**
     * Assign or reassign an editor to the submission.
     */
    public function assignEditor(Request $request, Submission $submission)
    {
        Gate::authorize('editorial-assign', [$submission]);

        $request->validate([
            'editor_id' => 'required|exists:users,id',
        ]);

        $editorId = $request->editor_id;

        $targetMembership = $submission->journal->memberships()
            ->where('user_id', $editorId)
            ->where('role', 'editor')
            ->where('status', 'active')
            ->first();

        if (!$targetMembership) {
            return $this->json('Target user is not a valid active editor for this journal.', null, 422);
        }

        $isReassignment = !is_null($submission->editor_id);

        DB::transaction(function () use ($submission, $editorId, $isReassignment) {
            $submission->forceFill(['editor_id' => $editorId])->save();

            $submission->editorialEvents()->create([
                'user_id' => auth()->id(),
                'action' => $isReassignment ? SubmissionEditorialEvent::ACTION_REASSIGNED : SubmissionEditorialEvent::ACTION_ASSIGNED,
                'payload' => ['editor_id' => $editorId],
            ]);
        });

        $submission->load(['editor', 'editorialEvents']);

        return $this->json('Editor assigned successfully.', new EditorialSubmissionResource($submission));
    }

    /**
     * Record an Editorial Decision.
     */
    public function recordEditorialDecision(Request $request, Submission $submission, \App\Models\ReviewRound $round)
    {
        Gate::authorize('editorial-process', [$submission]);

        if ($round->submissionRevision->submission_id !== $submission->id) {
            return $this->json('Round does not belong to this submission.', null, 422);
        }

        if ($round->editorialDecision) {
            return $this->json('Editorial Decision already recorded for this round.', null, 422);
        }

        $request->validate([
            'decision' => 'required|string',
            'comments' => 'nullable|string',
        ]);

        $decision = $request->decision;
        
        $statusMap = [
            'accept' => Submission::STATUS_ACCEPTED ?? 'accepted', // Use constant if defined
            'revision_required' => Submission::STATUS_REVISION_REQUIRED,
            'reject' => Submission::STATUS_REJECTED,
        ];

        if (!isset($statusMap[$decision])) {
            return $this->json('Invalid decision.', null, 422);
        }

        $targetStatus = $statusMap[$decision];

        DB::transaction(function () use ($submission, $round, $decision, $request, $targetStatus) {
            $round->editorialDecision()->create([
                'user_id' => auth()->id(),
                'decision' => $decision,
                'comments' => $request->comments,
            ]);

            $submission->forceFill(['status' => $targetStatus])->save();

            $submission->editorialEvents()->create([
                'user_id' => auth()->id(),
                'action' => 'editorial_decision_recorded',
                'payload' => ['decision' => $decision, 'round_id' => $round->id],
            ]);
        });

        $submission->load(['editorialEvents', 'revisions.reviewRounds.editorialDecision']);

        return $this->json('Editorial decision recorded successfully.', new EditorialSubmissionResource($submission));
    }

    /**
     * Start a new review round for the latest revision.
     */
    public function startReviewRound(Request $request, Submission $submission)
    {
        Gate::authorize('editorial-process', [$submission]);

        $latestRevision = $submission->revisions()->orderBy('version_number', 'desc')->first();
        if (!$latestRevision) {
            return $this->json('No revision found for submission.', null, 422);
        }

        $latestRound = $latestRevision->reviewRounds()->orderBy('round_number', 'desc')->first();
        
        if ($latestRound && !$latestRound->editorialDecision) {
            return $this->json('An active review round already exists for this revision.', null, 422);
        }

        DB::transaction(function () use ($submission, $latestRevision, $latestRound) {
            $newRoundNum = $latestRound ? $latestRound->round_number + 1 : 1;
            
            // Get current policy
            $policy = [
                'review_model' => 'double_blind',
                'minimum_reviewers' => 2,
                'target_reviewers' => 2,
                'maximum_reviewers' => 4,
            ];
            
            $journalPolicy = $submission->journal->peerReviewPolicy()->first();
            if ($journalPolicy) {
                $policy['review_model'] = $journalPolicy->review_model;
                $policy['minimum_reviewers'] = $journalPolicy->minimum_reviewers;
                $policy['target_reviewers'] = $journalPolicy->target_reviewers;
                $policy['maximum_reviewers'] = $journalPolicy->maximum_reviewers;
            }

            $latestRevision->reviewRounds()->create([
                'round_number' => $newRoundNum,
                'review_model' => $policy['review_model'],
                'minimum_reviewers' => $policy['minimum_reviewers'],
                'target_reviewers' => $policy['target_reviewers'],
                'maximum_reviewers' => $policy['maximum_reviewers'],
            ]);

            // Update submission status if needed
            if (!in_array($submission->status, [Submission::STATUS_REVIEW_PENDING, 'under_review'])) {
                $submission->forceFill(['status' => Submission::STATUS_REVIEW_PENDING])->save();
                
                $submission->editorialEvents()->create([
                    'user_id' => auth()->id(),
                    'action'  => 'status_transition',
                    'payload' => ['from' => $submission->getOriginal('status') ?? Submission::STATUS_REVIEW_PENDING, 'to' => Submission::STATUS_REVIEW_PENDING],
                ]);
            }
        });

        $submission->load(['authors', 'revisions.files', 'revisions.reviewRounds.assignments.reviewer', 'revisions.reviewRounds.assignments.peerReview', 'revisions.reviewRounds.editorialDecision', 'journal', 'editor', 'editorialEvents.user']);

        return $this->json('Review round started successfully.', new EditorialSubmissionResource($submission));
    }

    /**
     * Get eligible reviewers for the submission's journal.
     */
    public function eligibleReviewers(Submission $submission)
    {
        Gate::authorize('editorial-process', [$submission]);

        $reviewers = User::whereHas('journalMemberships', function ($query) use ($submission) {
            $query->where('journal_id', $submission->journal_id)
                  ->where('role', 'reviewer')
                  ->where('status', 'active')
                  ->where(function ($membershipQuery) use ($submission) {
                      $membershipQuery->whereHas('reviewerCapability', function ($capQuery) {
                          $capQuery->where('available_for_review', true);
                      })
                      ->orWhere(function ($fallbackQuery) use ($submission) {
                          $fallbackQuery->whereDoesntHave('reviewerCapability')
                                        ->whereHas('user.reviewerApplications', function ($appQuery) use ($submission) {
                                            $appQuery->where('journal_id', $submission->journal_id)
                                                     ->where('status', \App\Models\ReviewerApplication::STATUS_ACCEPTED);
                                        });
                      });
                  });
        })
        ->select('id', 'name', 'email')->get();

        return response()->json([
            'message' => 'Eligible reviewers retrieved successfully.',
            'data' => $reviewers
        ]);
    }

    /**
     * Assign a reviewer to the latest revision.
     */
    public function assignReviewer(Request $request, Submission $submission)
    {
        Gate::authorize('editorial-process', [$submission]);

        $request->validate([
            'reviewer_id' => 'required|exists:users,id',
            'review_mode' => 'nullable|in:single_blind,double_blind,open',
        ]);

        $reviewerId = $request->reviewer_id;

        $targetMembership = $submission->journal->memberships()
            ->where('user_id', $reviewerId)
            ->where('role', 'reviewer')
            ->where('status', 'active')
            ->first();

        if (!$targetMembership) {
            return $this->json('Target user is not a valid active reviewer for this journal.', null, 422);
        }

        $capability = $targetMembership->reviewerCapability;
        if ($capability) {
            if (!$capability->available_for_review) {
                return $this->json('Reviewer is not currently available for review.', null, 422);
            }
        } else {
            $legacyApp = \App\Models\ReviewerApplication::where('user_id', $reviewerId)
                ->where('journal_id', $submission->journal_id)
                ->where('status', \App\Models\ReviewerApplication::STATUS_ACCEPTED)
                ->first();
            if (!$legacyApp) {
                return $this->json('Reviewer is missing required capability or legacy application.', null, 422);
            }
        }

        DB::transaction(function () use ($submission, $reviewerId, $request) {
            $latestRevision = $submission->revisions()->orderBy('version_number', 'desc')->first();
            if (!$latestRevision) {
                throw new \Exception('No revision found for submission.');
            }

            // Get or create latest round for this revision
            $latestRound = $latestRevision->reviewRounds()->orderBy('round_number', 'desc')->first();
            
            // If the latest round already has a decision, we might want to create a new round on the same revision.
            if (!$latestRound || $latestRound->editorialDecision) {
                $newRoundNum = $latestRound ? $latestRound->round_number + 1 : 1;
                $latestRound = $latestRevision->reviewRounds()->create(['round_number' => $newRoundNum]);
            }

            // Check for duplicate active assignment
            $existing = \App\Models\ReviewAssignment::where('review_round_id', $latestRound->id)
                ->where('reviewer_id', $reviewerId)
                ->whereIn('status', ['assigned', 'accepted', 'in_progress'])
                ->first();
                
            if ($existing) {
                throw new \Exception('Reviewer is already actively assigned to this round.');
            }

            $currentAssignments = \App\Models\ReviewAssignment::where('review_round_id', $latestRound->id)
                ->whereIn('status', ['assigned', 'accepted', 'in_progress', 'submitted'])
                ->count();
                
            if ($currentAssignments >= $latestRound->maximum_reviewers) {
                throw new \Exception('Maximum number of reviewers reached.');
            }

            $assignment = \App\Models\ReviewAssignment::create([
                'review_round_id' => $latestRound->id,
                'reviewer_id' => $reviewerId,
                'assigned_by' => auth()->id(),
                'review_mode' => $request->review_mode ?? 'single_blind',
                'status' => 'assigned',
                'assigned_at' => now(),
            ]);

            $submission->editorialEvents()->create([
                'user_id' => auth()->id(),
                'action' => SubmissionEditorialEvent::ACTION_REVIEWER_ASSIGNED,
                'payload' => ['reviewer_id' => $reviewerId, 'assignment_id' => $assignment->id, 'round_id' => $latestRound->id],
            ]);
            
            // Update submission status if needed
            if ($submission->status !== Submission::STATUS_REVIEW_PENDING && $submission->status !== 'under_review') {
                $submission->forceFill(['status' => Submission::STATUS_REVIEW_PENDING])->save();
            }
        });

        $submission->load(['editorialEvents', 'revisions.reviewRounds.assignments']);

        return $this->json('Reviewer assigned successfully.');
    }

    /**
     * Cancel a reviewer assignment.
     */
    public function cancelReviewAssignment(Request $request, Submission $submission, \App\Models\ReviewAssignment $assignment)
    {
        Gate::authorize('cancel', $assignment);

        if ($assignment->reviewRound->submissionRevision->submission_id !== $submission->id) {
            return $this->json('Assignment does not belong to this submission.', null, 422);
        }

        if (in_array($assignment->status, ['submitted', 'cancelled', 'declined'])) {
            return $this->json('Assignment cannot be cancelled in its current state.', null, 422);
        }

        DB::transaction(function () use ($submission, $assignment) {
            $assignment->update([
                'status' => 'cancelled',
                'cancelled_at' => now(),
            ]);

            $submission->editorialEvents()->create([
                'user_id' => auth()->id(),
                'action' => SubmissionEditorialEvent::ACTION_REVIEWER_CANCELLED,
                'payload' => ['reviewer_id' => $assignment->reviewer_id, 'assignment_id' => $assignment->id],
            ]);
        });

        return $this->json('Review assignment cancelled successfully.');
    }
}
