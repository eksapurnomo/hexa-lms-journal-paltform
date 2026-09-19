<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\JournalMembership;
use App\Models\Submission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserDashboardController extends Controller
{
    /**
     * Retrieve the dashboard context for the authenticated user.
     */
    public function context(Request $request)
    {
        $user = Auth::user();

        // 1. Determine Author Status (has_submissions)
        // Check if user is creator or co-author
        $submissionCount = Submission::where(function ($query) use ($user) {
            $query->where('created_by', $user->id)
                  ->orWhereHas('authors', function ($authorQuery) use ($user) {
                      $authorQuery->where('user_id', $user->id);
                  });
        })->count();

        // 2. Fetch Active Journal Memberships
        $membershipsData = JournalMembership::with('journal:id,title,slug')
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->get();

        $memberships = $membershipsData->map(function ($membership) {
            return [
                'journal' => $membership->journal,
                'role' => $membership->role,
                'status' => $membership->status,
            ];
        });

        // 3. Determine Managed Journals
        // Based on JournalPolicy, owners and editors have management capabilities
        $managedJournals = $membershipsData->filter(function ($membership) {
            return in_array($membership->role, ['owner', 'editor']);
        })->map(function ($membership) {
            return [
                'journal' => $membership->journal,
                'role' => $membership->role,
            ];
        })->values();

        return $this->json('Dashboard context retrieved successfully.', [
            'submission' => [
                'has_submissions' => $submissionCount > 0,
                'count' => $submissionCount,
            ],
            'memberships' => $memberships,
            'managed_journals' => $managedJournals,
        ]);
    }

    /**
     * Retrieve management overview context for a specific journal.
     */
    public function journalManagementOverview(Request $request, $slug)
    {
        $user = Auth::user();
        
        // Find journal by slug
        $journal = \App\Models\Journal::where('slug', $slug)->first();
        
        if (!$journal) {
            return response()->json(['message' => 'Journal not found'], 404);
        }

        if (!$user->can('manageMembers', $journal)) {
            return response()->json(['message' => 'Unauthorized journal management access'], 403);
        }

        // Check management capability (Owner or Editor)
        $membership = $journal->memberships()
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->whereIn('role', ['owner', 'editor'])
            ->first();

        // Editorial Snapshot (Journal level)
        $snapshot = [
            'new_submissions' => \App\Models\Submission::where('journal_id', $journal->id)->where('status', \App\Models\Submission::STATUS_SUBMITTED)->count(),
            'under_review' => \App\Models\Submission::where('journal_id', $journal->id)->where('status', \App\Models\Submission::STATUS_REVIEW_PENDING)->count(),
            'revision' => \App\Models\Submission::where('journal_id', $journal->id)->whereIn('status', [\App\Models\Submission::STATUS_REVISION_REQUIRED, \App\Models\Submission::STATUS_REVISION_SUBMITTED])->count(),
            'accepted' => \App\Models\Submission::where('journal_id', $journal->id)->where('status', \App\Models\Submission::STATUS_ACCEPTED)->count(),
            'rejected' => \App\Models\Submission::where('journal_id', $journal->id)->where('status', \App\Models\Submission::STATUS_REJECTED)->count(),
        ];

        // My Editorial Work (User level)
        $myWork = null;
        if ($membership->role === 'editor' || $membership->role === 'owner') {
            $myWork = [
                'assigned_submissions' => \App\Models\Submission::where('journal_id', $journal->id)
                    ->where('editor_id', $user->id)
                    ->whereNotIn('status', [\App\Models\Submission::STATUS_DRAFT, \App\Models\Submission::STATUS_ACCEPTED, \App\Models\Submission::STATUS_REJECTED])
                    ->count(),
                'pending_actions' => \App\Models\Submission::where('journal_id', $journal->id)
                    ->where('editor_id', $user->id)
                    ->whereIn('status', [\App\Models\Submission::STATUS_EDITORIAL_ASSESSMENT, \App\Models\Submission::STATUS_REVISION_SUBMITTED])
                    ->count(),
            ];
        }

        return response()->json([
            'message' => 'Journal management context retrieved successfully.',
            'data' => [
                'journal' => [
                    'id' => $journal->id,
                    'slug' => $journal->slug,
                    'title' => $journal->title,
                    'issn' => $journal->issn ?? null,
                    'eissn' => $journal->eissn ?? null,
                    'status' => $journal->status,
                ],
                'role' => $membership->role,
                'editorial_snapshot' => $snapshot,
                'my_editorial_work' => $myWork,
            ]
        ]);
    }

    public function journalSubmissions(Request $request, $slug)
    {
        $user = Auth::user();
        $journal = \App\Models\Journal::where('slug', $slug)->first();
        
        if (!$journal) {
            return response()->json(['message' => 'Journal not found'], 404);
        }

        if (!$user->can('manageMembers', $journal)) {
            return response()->json(['message' => 'Unauthorized journal management access'], 403);
        }

        $membership = $journal->memberships()->where('user_id', $user->id)
            ->where('status', 'active')
            ->whereIn('role', ['owner', 'editor'])
            ->first();

        $query = \App\Models\Submission::with(['authors', 'files', 'editor'])
            ->where('journal_id', $journal->id)
            ->where('status', '!=', \App\Models\Submission::STATUS_DRAFT);

        if ($membership->role === 'editor') {
            $query->where('editor_id', $user->id);
        }

        $sortField = $request->input('sort_by', 'id');
        $sortDirection = $request->input('sort_dir', 'desc');
        
        $allowedSorts = ['id', 'title', 'status', 'created_at', 'updated_at'];
        if (in_array($sortField, $allowedSorts) && in_array(strtolower($sortDirection), ['asc', 'desc'])) {
            $query->orderBy($sortField, $sortDirection);
        } else {
            $query->orderBy('id', 'desc');
        }

        $submissions = $query->paginate($request->per_page ?? 15);
        
        return \App\Http\Resources\EditorialSubmissionResource::collection($submissions);
    }

    public function journalSubmissionDetail(Request $request, $slug, \App\Models\Submission $submission)
    {
        $user = Auth::user();
        $journal = \App\Models\Journal::where('slug', $slug)->first();
        
        if (!$journal || $submission->journal_id !== $journal->id) {
            return response()->json(['message' => 'Not found'], 404);
        }

        if (!$user->can('manageMembers', $journal)) {
            return response()->json(['message' => 'Unauthorized journal management access'], 403);
        }

        if (!$user->can('view', $submission)) {
            return response()->json(['message' => 'Not found'], 404);
        }

        // Domain filtering: In the Editorial Workspace, an editor can ONLY view assignments they own.
        // Even if they are the author of a submission (which passes the view policy), they shouldn't manage it.
        $membership = $journal->memberships()->where('user_id', $user->id)
            ->where('status', 'active')
            ->whereIn('role', ['owner', 'editor'])
            ->first();

        if ($membership->role === 'editor' && $submission->editor_id !== $user->id) {
            return response()->json(['message' => 'Not found'], 404);
        }

        $submission->load(['authors', 'revisions.files', 'revisions.reviewRounds.assignments.reviewer', 'revisions.reviewRounds.assignments.peerReview', 'revisions.reviewRounds.editorialDecision', 'journal', 'editor', 'editorialEvents.user']);

        return response()->json([
            'message' => 'Submission detail retrieved successfully.',
            'data' => new \App\Http\Resources\EditorialSubmissionResource($submission)
        ]);
    }

    public function journalEditorialProcess(Request $request, $slug)
    {
        $user = Auth::user();
        $journal = \App\Models\Journal::where('slug', $slug)->first();
        
        if (!$journal) {
            return response()->json(['message' => 'Journal not found'], 404);
        }

        if (!$user->can('manageMembers', $journal)) {
            return response()->json(['message' => 'Unauthorized journal management access'], 403);
        }

        $membership = $journal->memberships()->where('user_id', $user->id)
            ->where('status', 'active')
            ->whereIn('role', ['owner', 'editor'])
            ->first();

        // We use JournalProcessFlowService for overall stats if Owner. 
        // If Editor, we scope to their assignments.
        $processFlowService = app(\App\Services\JournalProcessFlowService::class);
        
        if ($membership->role === 'owner') {
            $stats = [
                'membership' => $processFlowService->getMembershipStats($journal->id),
                'verification' => $processFlowService->getVerificationStats($journal->id),
                'editorial' => $processFlowService->getEditorialStats($journal->id),
                'reviewerSelection' => $processFlowService->getReviewerSelectionStats($journal->id),
                'peerReview' => $processFlowService->getPeerReviewStats($journal->id),
                'recommendation' => $processFlowService->getRecommendationStats($journal->id),
                'decision' => $processFlowService->getEditorialDecisionStats($journal->id),
            ];
            $currentProcess = $processFlowService->getCurrentProcess($journal->id);
        } else {
            // Editor scope
            // We manually query stats scoped to editor_id
            $assignedSubmissions = \App\Models\Submission::where('journal_id', $journal->id)
                ->where('editor_id', $user->id);
            
            $assignedSubmissionIds = $assignedSubmissions->pluck('id')->toArray();
            
            $assigned = count($assignedSubmissionIds);
            
            $roundsCount = \App\Models\ReviewRound::whereHas('submissionRevision', function ($q) use ($assignedSubmissionIds) {
                $q->whereIn('submission_id', $assignedSubmissionIds);
            })->count();

            $assignments = \App\Models\ReviewAssignment::whereHas('reviewRound.submissionRevision', function ($q) use ($assignedSubmissionIds) {
                $q->whereIn('submission_id', $assignedSubmissionIds);
            })->select('status', \Illuminate\Support\Facades\DB::raw('count(*) as count'))
              ->groupBy('status')
              ->pluck('count', 'status')
              ->toArray();

            $awaiting = $assignments['assigned'] ?? 0;
            $assignedReviewers = ($assignments['accepted'] ?? 0) + ($assignments['in_progress'] ?? 0) + ($assignments['submitted'] ?? 0);
            
            $peerReviewsPending = ($assignments['accepted'] ?? 0) + ($assignments['in_progress'] ?? 0);
            $peerReviewsCompleted = $assignments['submitted'] ?? 0;

            $peerReviews = \App\Models\PeerReview::whereHas('reviewAssignment.reviewRound.submissionRevision', function ($q) use ($assignedSubmissionIds) {
                $q->whereIn('submission_id', $assignedSubmissionIds);
            });
            $recommendationsSubmitted = $peerReviews->count();
            $recommendationsAwaiting = \App\Models\ReviewAssignment::whereHas('reviewRound.submissionRevision', function ($q) use ($assignedSubmissionIds) {
                $q->whereIn('submission_id', $assignedSubmissionIds);
            })->whereIn('status', ['accepted', 'in_progress'])->count();
            
            $rounds = \App\Models\ReviewRound::whereHas('submissionRevision', function ($q) use ($assignedSubmissionIds) {
                $q->whereIn('submission_id', $assignedSubmissionIds);
            })->get();
            $decisionsCompleted = \App\Models\EditorialDecision::whereIn('review_round_id', $rounds->pluck('id'))->count();
            $totalRounds = $rounds->count();
            $decisionsPending = max(0, $totalRounds - $decisionsCompleted);

            $stats = [
                'editorial' => [
                    'assigned' => $assigned,
                    'unassigned' => 0, // Editors only see their own unassigned doesn't apply
                    'total' => $assigned,
                ],
                'reviewerSelection' => [
                    'rounds' => $roundsCount,
                    'awaiting' => $awaiting,
                    'assigned' => $assignedReviewers,
                ],
                'peerReview' => [
                    'pending' => $peerReviewsPending,
                    'completed' => $peerReviewsCompleted,
                ],
                'recommendation' => [
                    'awaiting' => $recommendationsAwaiting,
                    'submitted' => $recommendationsSubmitted,
                ],
                'decision' => [
                    'pending' => $decisionsPending,
                    'completed' => $decisionsCompleted,
                ],
            ];
            
            // Re-evaluate current process for editor
            $process = 'Monitoring Operations';
            $blocker = 'No immediate action required.';
            
            if ($decisionsPending > 0 && $recommendationsSubmitted > 0 && $recommendationsAwaiting === 0) {
                $process = 'Editorial Decision Pending';
                $blocker = $decisionsPending . ' round(s) waiting for final decision.';
            } else if ($recommendationsAwaiting > 0 || $peerReviewsPending > 0) {
                $totalWaiting = $recommendationsAwaiting + $peerReviewsPending;
                $process = 'Waiting for Peer Review';
                $blocker = $totalWaiting . ' review(s) pending completion.';
            } else if ($roundsCount > 0 && $assignedReviewers === 0) {
                $process = 'Reviewer Selection';
                $blocker = $roundsCount . ' round(s) require reviewer assignment.';
            }

            $currentProcess = [
                'process' => $process,
                'blocker' => $blocker
            ];
        }

        return response()->json([
            'message' => 'Journal editorial process retrieved successfully.',
            'data' => [
                'journal' => [
                    'id' => $journal->id,
                    'slug' => $journal->slug,
                    'title' => $journal->title,
                ],
                'role' => $membership->role,
                'stats' => $stats,
                'current_process' => $currentProcess,
            ]
        ]);
    }

    public function journalMembers(Request $request, $slug)
    {
        $user = Auth::user();
        $journal = \App\Models\Journal::where('slug', $slug)->first();
        
        if (!$journal) {
            return response()->json(['message' => 'Journal not found'], 404);
        }

        if (!$user->can('manageMembers', $journal)) {
            return response()->json(['message' => 'Unauthorized journal management access'], 403);
        }

        $members = $journal->memberships()->with(['user' => function($query) {
            $query->select('id', 'name', 'email');
        }, 'reviewerCapability'])->get();

        return response()->json([
            'message' => 'Journal members retrieved successfully.',
            'data' => $members->map(function ($membership) {
                return [
                    'id' => $membership->id,
                    'user' => $membership->user,
                    'role' => $membership->role,
                    'status' => $membership->status,
                    'created_at' => $membership->created_at,
                    'reviewer_capability' => $membership->reviewerCapability,
                ];
            })
        ]);
    }

    public function journalSettings(Request $request, $slug)
    {
        $user = Auth::user();
        $journal = \App\Models\Journal::where('slug', $slug)->first();
        
        if (!$journal) {
            return response()->json(['message' => 'Journal not found'], 404);
        }

        if (!$user->can('update', $journal)) {
            return response()->json(['message' => 'Unauthorized journal management access'], 403);
        }

        return response()->json([
            'message' => 'Journal settings retrieved successfully.',
            'data' => [
                'id' => $journal->id,
                'slug' => $journal->slug,
                'title' => $journal->title,
                'description' => $journal->description,
                'issn' => $journal->issn,
                'eissn' => $journal->eissn,
                'status' => $journal->status,
            ]
        ]);
    }

    public function updateJournalSettings(Request $request, $slug)
    {
        $user = Auth::user();
        $journal = \App\Models\Journal::where('slug', $slug)->first();
        
        if (!$journal) {
            return response()->json(['message' => 'Journal not found'], 404);
        }

        if (!$user->can('update', $journal)) {
            return response()->json(['message' => 'Unauthorized journal management access'], 403);
        }

        // We only allow title, description, issn, eissn. Status requires explicit domain rules.
        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'issn' => 'nullable|string|max:50',
            'eissn' => 'nullable|string|max:50',
        ]);

        $journal->update($validated);

        return response()->json([
            'message' => 'Journal settings updated successfully.',
            'data' => [
                'id' => $journal->id,
                'slug' => $journal->slug,
                'title' => $journal->title,
                'description' => $journal->description,
                'issn' => $journal->issn,
                'eissn' => $journal->eissn,
                'status' => $journal->status,
            ]
        ]);
    }
}

