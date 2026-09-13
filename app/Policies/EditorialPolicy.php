<?php

namespace App\Policies;

use App\Models\Submission;
use App\Models\User;

class EditorialPolicy
{
    /**
     * Determine whether the user can process the submission.
     */
    public function process(User $user, Submission $submission): bool
    {
        if ($user->hasRole('admin') || $user->is_admin) {
            return true;
        }

        $membership = $submission->journal->memberships()
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->first();

        if (!$membership) {
            return false;
        }

        if ($membership->role === 'owner') {
            return true;
        }

        if ($membership->role === 'editor' && $submission->editor_id === $user->id) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can assign an editor.
     */
    public function assign(User $user, Submission $submission): bool
    {
        if ($user->hasRole('admin') || $user->is_admin) {
            return true;
        }

        $membership = $submission->journal->memberships()
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->first();

        if (!$membership) {
            return false;
        }

        return $membership->role === 'owner';
    }
}
