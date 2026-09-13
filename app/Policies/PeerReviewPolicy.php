<?php

namespace App\Policies;

use App\Models\PeerReview;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class PeerReviewPolicy
{
    use HandlesAuthorization;

    public function view(User $user, PeerReview $peerReview): Response
    {
        $assignment = $peerReview->reviewAssignment;
        
        // Reviewer can view their own review
        if ($assignment->reviewer_id === $user->id) {
            return Response::allow();
        }

        // Admins/Owners/Assigned Editor can view (based on Phase 5E editorial-process logic)
        if ($user->can('editorial-process', $assignment->submission)) {
            return Response::allow();
        }

        return Response::deny('You do not have access to this peer review.');
    }

    public function update(User $user, PeerReview $peerReview): Response
    {
        $assignment = $peerReview->reviewAssignment;

        if ($assignment->reviewer_id !== $user->id) {
            return Response::deny('Only the assigned reviewer can modify this review.');
        }

        if ($assignment->status === 'submitted') {
            return Response::deny('This review has already been submitted and is immutable.');
        }

        return Response::allow();
    }
}
