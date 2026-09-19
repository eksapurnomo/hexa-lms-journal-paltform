<?php

namespace App\Policies;

use App\Models\Journal;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class JournalPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        if ($user->hasRole('admin') || $user->is_admin) {
            return true;
        }

        // Users can view the index (which will be filtered to journals they belong to)
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Journal $journal): bool
    {
        if ($user->hasRole('admin') || $user->is_admin) {
            return true;
        }

        return $journal->memberships()->where('user_id', $user->id)->where('status', 'active')->exists();
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasRole('admin') || $user->is_admin;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Journal $journal): bool
    {
        if ($user->hasRole('admin') || $user->is_admin) {
            return true;
        }

        $membership = $journal->memberships()->where('user_id', $user->id)->where('status', 'active')->first();
        if (!$membership) {
            return false;
        }

        return $membership->role === 'owner';
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Journal $journal): bool
    {
        if ($user->hasRole('admin') || $user->is_admin) {
            return true;
        }

        $membership = $journal->memberships()->where('user_id', $user->id)->where('status', 'active')->first();
        if (!$membership) {
            return false;
        }

        return $membership->role === 'owner';
    }

    /**
     * Determine whether the user can manage members.
     */
    public function manageMembers(User $user, Journal $journal): bool
    {
        if ($user->hasRole('admin') || $user->is_admin) {
            return true;
        }

        $membership = $journal->memberships()->where('user_id', $user->id)->where('status', 'active')->first();
        if (!$membership) {
            return false;
        }

        return in_array($membership->role, ['owner', 'editor']);
    }
}
