<?php

namespace App\Policies;

use App\Models\ReviewAssignment;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class ReviewAssignmentPolicy
{
    use HandlesAuthorization;

    public function view(User $user, ReviewAssignment $assignment): Response
    {
        // Assigned reviewer can view
        if ($assignment->reviewer_id === $user->id) {
            return Response::allow();
        }

        // Admins/Owners/Assigned Editor can view (based on Phase 5E editorial-process logic)
        // Here we just delegate to the submission's editorial-process gate.
        $submission = $assignment->reviewRound->submissionRevision->submission;
        if ($user->can('editorial-process', $submission)) {
            return Response::allow();
        }

        return Response::deny('You do not have access to this assignment.');
    }

    public function update(User $user, ReviewAssignment $assignment): Response
    {
        // Only assigned reviewer can accept/decline/start/submit
        if ($assignment->reviewer_id === $user->id) {
            return Response::allow();
        }

        return Response::deny('Only the assigned reviewer can modify this assignment.');
    }

    public function cancel(User $user, ReviewAssignment $assignment): Response
    {
        // Only someone with editorial-process rights can cancel an assignment
        $submission = $assignment->reviewRound->submissionRevision->submission;
        if ($user->can('editorial-process', $submission)) {
            return Response::allow();
        }

        return Response::deny('You do not have permission to cancel this assignment.');
    }
}
