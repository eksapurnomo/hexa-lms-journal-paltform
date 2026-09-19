<?php

namespace App\Services;

use App\Models\User;
use App\Models\JournalMembership;
use App\Models\JournalMembershipApplication;
use App\Models\Submission;
use App\Models\ReviewAssignment;
use App\Models\PeerReview;
use App\Models\ReviewRound;
use App\Models\EditorialDecision;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class JournalUserProcessMonitorService
{
    /**
     * Get a deduplicated list of users associated with a journal, 
     * either via active/pending membership or verification applications.
     */
    public function getJournalUsers(int $journalId): Collection
    {
        $membershipUserIds = JournalMembership::where('journal_id', $journalId)->pluck('user_id');
        $applicationUserIds = JournalMembershipApplication::where('journal_id', $journalId)->pluck('user_id');

        $userIds = $membershipUserIds->merge($applicationUserIds)->unique();

        return User::whereIn('id', $userIds)->get();
    }

    /**
     * Retrieve the roles applicable to a user for a specific journal.
     * Returns an array of roles (e.g. ['editor', 'reviewer']).
     */
    public function getUserRoles(int $journalId, int $userId): array
    {
        $membershipRoles = JournalMembership::where('journal_id', $journalId)
            ->where('user_id', $userId)
            ->pluck('role')
            ->toArray();

        $appRoles = JournalMembershipApplication::where('journal_id', $journalId)
            ->where('user_id', $userId)
            ->pluck('requested_role')
            ->toArray();

        return collect($membershipRoles)->merge($appRoles)->unique()->values()->toArray();
    }

    /**
     * Get the process node array for a specific user and role in a journal.
     */
    public function getUserProcess(int $journalId, int $userId, string $role): array
    {
        if ($role === 'editor' || $role === 'owner') {
            return $this->getEditorProcess($journalId, $userId, $role);
        } elseif ($role === 'reviewer') {
            return $this->getReviewerProcess($journalId, $userId);
        } else {
            // Author flow or unknown
            return [
                'nodes' => [],
                'current_process' => 'Unknown Role',
                'current_blocker' => 'No workflow defined for this role.',
                'progress_text' => '0 / 0',
                'progress_percent' => 0
            ];
        }
    }

    private function getEditorProcess(int $journalId, int $userId, string $role): array
    {
        $nodes = [];
        $app = JournalMembershipApplication::where('journal_id', $journalId)
            ->where('user_id', $userId)
            ->where('requested_role', $role)
            ->latest()
            ->first();

        $membership = JournalMembership::where('journal_id', $journalId)
            ->where('user_id', $userId)
            ->where('role', $role)
            ->first();

        // Node 1: Membership Application
        $appStatus = $app ? $app->status : null;
        if (!$app && $membership) {
            $node1 = ['name' => 'Membership Application', 'state' => '✓ Completed'];
        } else {
            if ($appStatus === 'draft') $node1 = ['name' => 'Membership Application', 'state' => '● Active'];
            elseif ($appStatus === 'submitted' || $appStatus === 'under_review' || $appStatus === 'needs_revision') $node1 = ['name' => 'Membership Application', 'state' => '✓ Completed'];
            elseif ($appStatus === 'approved') $node1 = ['name' => 'Membership Application', 'state' => '✓ Completed'];
            elseif ($appStatus === 'rejected') $node1 = ['name' => 'Membership Application', 'state' => '⛔ Blocked'];
            else $node1 = ['name' => 'Membership Application', 'state' => '○ Pending'];
        }
        $nodes[] = $node1;

        // Node 2: Identity & Academic Verification
        if (!$app && $membership) {
            $node2 = ['name' => 'Identity & Academic Verification', 'state' => '✓ Completed'];
        } else {
            if ($appStatus === 'submitted' || $appStatus === 'under_review') $node2 = ['name' => 'Identity & Academic Verification', 'state' => '● Active'];
            elseif ($appStatus === 'needs_revision') $node2 = ['name' => 'Identity & Academic Verification', 'state' => '⚠ Needs Action'];
            elseif ($appStatus === 'approved') $node2 = ['name' => 'Identity & Academic Verification', 'state' => '✓ Completed'];
            elseif ($appStatus === 'rejected') $node2 = ['name' => 'Identity & Academic Verification', 'state' => '⛔ Blocked'];
            else $node2 = ['name' => 'Identity & Academic Verification', 'state' => '○ Pending'];
        }
        $nodes[] = $node2;

        // Node 3: Active Journal Membership
        $isActive = $membership && $membership->status === 'active';
        if ($isActive) {
            $node3 = ['name' => 'Active Journal Membership', 'state' => '✓ Completed'];
        } elseif ($membership && $membership->status === 'pending') {
            $node3 = ['name' => 'Active Journal Membership', 'state' => '○ Pending'];
        } elseif ($membership && in_array($membership->status, ['suspended', 'revoked'])) {
            $node3 = ['name' => 'Active Journal Membership', 'state' => '⛔ Blocked'];
        } else {
            $node3 = ['name' => 'Active Journal Membership', 'state' => '○ Pending'];
        }
        $nodes[] = $node3;

        // Node 4: Editorial Access
        $hasAccess = $isActive;
        if ($hasAccess) {
            $node4 = ['name' => 'Editorial Access', 'state' => '✓ Completed'];
        } else {
            $node4 = ['name' => 'Editorial Access', 'state' => '○ Pending'];
        }
        $nodes[] = $node4;

        // Node 5-10 require checking actual submissions assigned to this editor in this journal
        $submissions = Submission::where('journal_id', $journalId)
            ->where('editor_id', $userId)
            ->get();
            
        $hasSubmissions = $submissions->isNotEmpty();

        // Node 5: Submission / Editorial Processing
        if (!$hasAccess) {
            $node5 = ['name' => 'Submission / Editorial Processing', 'state' => '○ Pending'];
        } else {
            if ($hasSubmissions) {
                $node5 = ['name' => 'Submission / Editorial Processing', 'state' => '✓ Completed'];
            } else {
                $node5 = ['name' => 'Submission / Editorial Processing', 'state' => '● Active']; // Waiting for assignments
            }
        }
        $nodes[] = $node5;

        // Fetch Rounds
        $roundIds = ReviewRound::whereHas('submissionRevision.submission', function ($q) use ($journalId, $userId) {
            $q->where('journal_id', $journalId)->where('editor_id', $userId);
        })->pluck('id');

        $hasRounds = $roundIds->isNotEmpty();
        
        // Node 6: Reviewer Selection
        if (!$hasAccess || !$hasSubmissions) {
            $node6 = ['name' => 'Reviewer Selection', 'state' => '○ Pending'];
        } else {
            if ($hasRounds) {
                $node6 = ['name' => 'Reviewer Selection', 'state' => '✓ Completed'];
            } else {
                $node6 = ['name' => 'Reviewer Selection', 'state' => '● Active']; // Editor needs to create round
            }
        }
        $nodes[] = $node6;

        // Node 7: Peer Review
        $assignments = ReviewAssignment::whereIn('review_round_id', $roundIds)->get();
        $hasAssignments = $assignments->isNotEmpty();
        $completedReviews = $assignments->where('status', 'submitted')->count();
        
        if (!$hasRounds) {
            $node7 = ['name' => 'Peer Review', 'state' => '○ Pending'];
        } else {
            if ($hasAssignments && $completedReviews > 0) {
                $node7 = ['name' => 'Peer Review', 'state' => '✓ Completed'];
            } elseif ($hasAssignments) {
                $node7 = ['name' => 'Peer Review', 'state' => '● Active'];
            } else {
                $node7 = ['name' => 'Peer Review', 'state' => '⚠ Needs Action']; // No assignments made yet
            }
        }
        $nodes[] = $node7;

        // Node 8: Recommendation
        $hasRecommendations = PeerReview::whereHas('reviewAssignment', function ($q) use ($roundIds) {
            $q->whereIn('review_round_id', $roundIds);
        })->whereNotNull('recommendation')->exists();

        if (!$hasAssignments) {
            $node8 = ['name' => 'Recommendation', 'state' => '○ Pending'];
        } else {
            if ($hasRecommendations) {
                $node8 = ['name' => 'Recommendation', 'state' => '✓ Completed'];
            } else {
                $node8 = ['name' => 'Recommendation', 'state' => '● Active'];
            }
        }
        $nodes[] = $node8;

        // Node 9: Editorial Decision
        $hasDecisions = EditorialDecision::whereIn('review_round_id', $roundIds)->exists();
        if (!$hasRecommendations) {
            $node9 = ['name' => 'Editorial Decision', 'state' => '○ Pending'];
        } else {
            if ($hasDecisions) {
                $node9 = ['name' => 'Editorial Decision', 'state' => '✓ Completed'];
            } else {
                $node9 = ['name' => 'Editorial Decision', 'state' => '● Active'];
            }
        }
        $nodes[] = $node9;

        // Node 10: Publication (Does not exist in domain yet, marking as N/A or completed if decision made)
        if (!$hasDecisions) {
            $node10 = ['name' => 'Publication', 'state' => '○ Pending'];
        } else {
            $node10 = ['name' => 'Publication', 'state' => '— Not Applicable'];
        }
        $nodes[] = $node10;

        return $this->summarizeNodes($nodes, $appStatus);
    }

    private function getReviewerProcess(int $journalId, int $userId): array
    {
        $nodes = [];
        $app = JournalMembershipApplication::where('journal_id', $journalId)
            ->where('user_id', $userId)
            ->where('requested_role', 'reviewer')
            ->latest()
            ->first();

        $membership = JournalMembership::where('journal_id', $journalId)
            ->where('user_id', $userId)
            ->where('role', 'reviewer')
            ->first();

        // Node 1: Membership Application
        $appStatus = $app ? $app->status : null;
        if (!$app && $membership) {
            $node1 = ['name' => 'Membership Application', 'state' => '✓ Completed'];
        } else {
            if ($appStatus === 'draft') $node1 = ['name' => 'Membership Application', 'state' => '● Active'];
            elseif ($appStatus === 'submitted' || $appStatus === 'under_review' || $appStatus === 'needs_revision') $node1 = ['name' => 'Membership Application', 'state' => '✓ Completed'];
            elseif ($appStatus === 'approved') $node1 = ['name' => 'Membership Application', 'state' => '✓ Completed'];
            elseif ($appStatus === 'rejected') $node1 = ['name' => 'Membership Application', 'state' => '⛔ Blocked'];
            else $node1 = ['name' => 'Membership Application', 'state' => '○ Pending'];
        }
        $nodes[] = $node1;

        // Node 2: Identity & Academic Verification
        if (!$app && $membership) {
            $node2 = ['name' => 'Identity & Academic Verification', 'state' => '✓ Completed'];
        } else {
            if ($appStatus === 'submitted' || $appStatus === 'under_review') $node2 = ['name' => 'Identity & Academic Verification', 'state' => '● Active'];
            elseif ($appStatus === 'needs_revision') $node2 = ['name' => 'Identity & Academic Verification', 'state' => '⚠ Needs Action'];
            elseif ($appStatus === 'approved') $node2 = ['name' => 'Identity & Academic Verification', 'state' => '✓ Completed'];
            elseif ($appStatus === 'rejected') $node2 = ['name' => 'Identity & Academic Verification', 'state' => '⛔ Blocked'];
            else $node2 = ['name' => 'Identity & Academic Verification', 'state' => '○ Pending'];
        }
        $nodes[] = $node2;

        // Node 3: Reviewer Membership Active
        $isActive = $membership && $membership->status === 'active';
        if ($isActive) {
            $node3 = ['name' => 'Reviewer Membership Active', 'state' => '✓ Completed'];
        } elseif ($membership && $membership->status === 'pending') {
            $node3 = ['name' => 'Reviewer Membership Active', 'state' => '○ Pending'];
        } elseif ($membership && in_array($membership->status, ['suspended', 'revoked'])) {
            $node3 = ['name' => 'Reviewer Membership Active', 'state' => '⛔ Blocked'];
        } else {
            $node3 = ['name' => 'Reviewer Membership Active', 'state' => '○ Pending'];
        }
        $nodes[] = $node3;

        // Node 4: Reviewer Pool / Eligible Reviewer
        if ($isActive) {
            $node4 = ['name' => 'Reviewer Pool / Eligible Reviewer', 'state' => '✓ Completed'];
        } else {
            $node4 = ['name' => 'Reviewer Pool / Eligible Reviewer', 'state' => '○ Pending'];
        }
        $nodes[] = $node4;

        $assignments = ReviewAssignment::whereHas('reviewRound.submissionRevision.submission', function ($q) use ($journalId) {
            $q->where('journal_id', $journalId);
        })->where('reviewer_id', $userId)->get();
        
        $hasAssignments = $assignments->isNotEmpty();
        $completedAssignments = $assignments->where('status', 'submitted')->count();
        $pendingAssignments = $assignments->whereIn('status', ['assigned', 'accepted', 'in_progress'])->count();

        // Node 5: Review Assignment
        if (!$hasAssignments && $isActive) {
            $node5 = ['name' => 'Review Assignment', 'state' => '● Active']; // Waiting for assignment
        } elseif (!$hasAssignments) {
            $node5 = ['name' => 'Review Assignment', 'state' => '○ Pending'];
        } else {
            $node5 = ['name' => 'Review Assignment', 'state' => '✓ Completed'];
        }
        $nodes[] = $node5;

        // Node 6: Peer Review
        if (!$hasAssignments) {
            $node6 = ['name' => 'Peer Review', 'state' => '○ Pending'];
        } else {
            if ($completedAssignments > 0 && $pendingAssignments === 0) {
                $node6 = ['name' => 'Peer Review', 'state' => '✓ Completed'];
            } else {
                $node6 = ['name' => 'Peer Review', 'state' => '● Active'];
            }
        }
        $nodes[] = $node6;

        // Node 7: Recommendation
        $hasRecommendations = PeerReview::whereIn('review_assignment_id', $assignments->pluck('id'))->whereNotNull('recommendation')->exists();
        if (!$hasAssignments) {
            $node7 = ['name' => 'Recommendation', 'state' => '○ Pending'];
        } else {
            if ($hasRecommendations && $pendingAssignments === 0) {
                $node7 = ['name' => 'Recommendation', 'state' => '✓ Completed'];
            } else {
                $node7 = ['name' => 'Recommendation', 'state' => '● Active'];
            }
        }
        $nodes[] = $node7;

        return $this->summarizeNodes($nodes, $appStatus);
    }

    private function summarizeNodes(array $nodes, ?string $appStatus): array
    {
        $completed = 0;
        $total = 0;
        $currentProcess = 'Unknown';
        $currentBlocker = 'No blocker detected.';
        $foundActive = false;

        foreach ($nodes as $node) {
            if ($node['state'] !== '— Not Applicable') {
                $total++;
            }
            if ($node['state'] === '✓ Completed') {
                $completed++;
            }
            if (!$foundActive && in_array($node['state'], ['● Active', '⚠ Needs Action', '⛔ Blocked'])) {
                $currentProcess = $node['name'];
                $foundActive = true;
                
                if ($node['state'] === '⚠ Needs Action') {
                    if ($node['name'] === 'Identity & Academic Verification' && $appStatus === 'needs_revision') {
                        $currentBlocker = 'Membership application requires revision.';
                    } else {
                        $currentBlocker = 'Action required by user.';
                    }
                } elseif ($node['state'] === '⛔ Blocked') {
                    $currentBlocker = 'Process blocked. Check status.';
                }
            }
        }

        if (!$foundActive && $completed === $total && $total > 0) {
            $currentProcess = 'All Operations Completed';
        }

        return [
            'nodes' => $nodes,
            'current_process' => $currentProcess,
            'current_blocker' => $currentBlocker,
            'progress_text' => "$completed / $total",
            'progress_percent' => $total > 0 ? round(($completed / $total) * 100) : 0,
            'completed' => $completed,
            'total' => $total,
        ];
    }
}
