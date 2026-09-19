<?php

namespace App\Services;

use App\Models\JournalMembership;
use App\Models\JournalMembershipApplication;
use App\Models\Submission;
use App\Models\ReviewRound;
use App\Models\ReviewAssignment;
use App\Models\PeerReview;
use App\Models\EditorialDecision;
use Illuminate\Support\Facades\DB;

class JournalProcessFlowService
{
    /**
     * Get Membership statistics for a journal.
     */
    public function getMembershipStats(int $journalId): array
    {
        $counts = JournalMembership::where('journal_id', $journalId)
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        return [
            'active' => $counts['active'] ?? 0,
            'pending' => $counts['pending'] ?? 0,
            'suspended' => $counts['suspended'] ?? 0,
            'revoked' => $counts['revoked'] ?? 0,
            'total' => array_sum($counts),
        ];
    }

    /**
     * Get Verification statistics for a journal.
     */
    public function getVerificationStats(int $journalId): array
    {
        $counts = JournalMembershipApplication::where('journal_id', $journalId)
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        return [
            'draft' => $counts['draft'] ?? 0,
            'submitted' => $counts['submitted'] ?? 0,
            'under_review' => $counts['under_review'] ?? 0,
            'needs_revision' => $counts['needs_revision'] ?? 0,
            'approved' => $counts['approved'] ?? 0,
            'rejected' => $counts['rejected'] ?? 0,
            'total' => array_sum($counts),
        ];
    }

    /**
     * Get Editorial assignment statistics for a journal.
     */
    public function getEditorialStats(int $journalId): array
    {
        $assigned = Submission::where('journal_id', $journalId)
            ->whereNotNull('editor_id')
            ->count();
            
        $unassigned = Submission::where('journal_id', $journalId)
            ->whereNull('editor_id')
            ->count();

        return [
            'assigned' => $assigned,
            'unassigned' => $unassigned,
            'total' => $assigned + $unassigned,
        ];
    }

    /**
     * Get Reviewer Selection statistics.
     */
    public function getReviewerSelectionStats(int $journalId): array
    {
        // To find ReviewRounds for a journal, we join through SubmissionRevision -> Submission
        $roundsCount = ReviewRound::whereHas('submissionRevision.submission', function ($q) use ($journalId) {
            $q->where('journal_id', $journalId);
        })->count();

        $assignments = ReviewAssignment::whereHas('reviewRound.submissionRevision.submission', function ($q) use ($journalId) {
            $q->where('journal_id', $journalId);
        })
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        $awaiting = $assignments['assigned'] ?? 0;
        $assigned = ($assignments['accepted'] ?? 0) + ($assignments['in_progress'] ?? 0) + ($assignments['submitted'] ?? 0);

        return [
            'rounds' => $roundsCount,
            'awaiting' => $awaiting,
            'assigned' => $assigned,
        ];
    }

    /**
     * Get Peer Review statistics.
     */
    public function getPeerReviewStats(int $journalId): array
    {
        $assignments = ReviewAssignment::whereHas('reviewRound.submissionRevision.submission', function ($q) use ($journalId) {
            $q->where('journal_id', $journalId);
        })
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        $pending = ($assignments['accepted'] ?? 0) + ($assignments['in_progress'] ?? 0);
        $completed = $assignments['submitted'] ?? 0;

        return [
            'pending' => $pending,
            'completed' => $completed,
        ];
    }

    /**
     * Get Recommendation statistics.
     * Recommendation is part of PeerReview.
     */
    public function getRecommendationStats(int $journalId): array
    {
        $peerReviews = PeerReview::whereHas('reviewAssignment.reviewRound.submissionRevision.submission', function ($q) use ($journalId) {
            $q->where('journal_id', $journalId);
        });
        
        $submittedCount = $peerReviews->count();
        
        // Awaiting recommendation: assignments that are accepted or in_progress, meaning reviewers haven't submitted
        $awaitingCount = ReviewAssignment::whereHas('reviewRound.submissionRevision.submission', function ($q) use ($journalId) {
            $q->where('journal_id', $journalId);
        })
        ->whereIn('status', ['accepted', 'in_progress'])
        ->count();

        return [
            'awaiting' => $awaitingCount,
            'submitted' => $submittedCount,
        ];
    }

    /**
     * Get Editorial Decision statistics.
     */
    public function getEditorialDecisionStats(int $journalId): array
    {
        $rounds = ReviewRound::whereHas('submissionRevision.submission', function ($q) use ($journalId) {
            $q->where('journal_id', $journalId);
        })->get();

        $completed = EditorialDecision::whereIn('review_round_id', $rounds->pluck('id'))->count();
        $totalRounds = $rounds->count();
        $pending = max(0, $totalRounds - $completed);

        return [
            'pending' => $pending,
            'completed' => $completed,
        ];
    }

    /**
     * Get Current Process heuristic and blocker.
     */
    public function getCurrentProcess(int $journalId): array
    {
        $editorialStats = $this->getEditorialStats($journalId);
        $reviewerSelectionStats = $this->getReviewerSelectionStats($journalId);
        $peerReviewStats = $this->getPeerReviewStats($journalId);
        $recommendationStats = $this->getRecommendationStats($journalId);
        $decisionStats = $this->getEditorialDecisionStats($journalId);

        if ($decisionStats['pending'] > 0 && $recommendationStats['submitted'] > 0 && $recommendationStats['awaiting'] === 0) {
            return [
                'process' => 'Editorial Decision Pending',
                'blocker' => $decisionStats['pending'] . ' round(s) waiting for final decision.'
            ];
        }
        
        if ($recommendationStats['awaiting'] > 0 || $peerReviewStats['pending'] > 0) {
            $totalWaiting = $recommendationStats['awaiting'] + $peerReviewStats['pending'];
            return [
                'process' => 'Waiting for Peer Review',
                'blocker' => $totalWaiting . ' review(s) pending completion.'
            ];
        }
        
        if ($reviewerSelectionStats['rounds'] > 0 && $reviewerSelectionStats['assigned'] === 0) {
            return [
                'process' => 'Reviewer Selection',
                'blocker' => $reviewerSelectionStats['rounds'] . ' round(s) require reviewer assignment.'
            ];
        }

        if ($editorialStats['unassigned'] > 0) {
            return [
                'process' => 'Editorial Desk Triage',
                'blocker' => $editorialStats['unassigned'] . ' submission(s) waiting for editor assignment.'
            ];
        }

        return [
            'process' => 'Monitoring Operations',
            'blocker' => 'No immediate action required.'
        ];
    }
}
