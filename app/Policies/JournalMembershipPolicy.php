<?php

namespace App\Policies;

use App\Models\JournalMembership;
use App\Models\User;
use App\Models\Journal;
use Illuminate\Auth\Access\HandlesAuthorization;

class JournalMembershipPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any memberships for the journal.
     */
    public function viewAny(User $user, Journal $journal)
    {
        if ($user->hasRole('admin') || $user->is_admin) {
            return true;
        }

        // Journal Owners can view memberships of their own journal
        return $journal->memberships()->where('user_id', $user->id)->where('role', 'owner')->where('status', 'active')->exists();
    }

    /**
     * Determine whether the user can create a membership.
     */
    public function create(User $user, Journal $journal)
    {
        if ($user->hasRole('admin') || $user->is_admin) {
            return true;
        }

        // Only Journal Owners can create memberships for their journal
        return $journal->memberships()->where('user_id', $user->id)->where('role', 'owner')->where('status', 'active')->exists();
    }

    /**
     * Determine whether the user can update a membership.
     */
    public function update(User $user, JournalMembership $membership)
    {
        if ($user->hasRole('admin') || $user->is_admin) {
            return true;
        }

        // Only Journal Owners can update memberships for their journal
        return $membership->journal->memberships()->where('user_id', $user->id)->where('role', 'owner')->where('status', 'active')->exists();
    }

    /**
     * Determine whether the user can delete a membership.
     */
    public function delete(User $user, JournalMembership $membership)
    {
        if ($user->hasRole('admin') || $user->is_admin) {
            return true;
        }

        // Only Journal Owners can delete memberships for their journal
        return $membership->journal->memberships()->where('user_id', $user->id)->where('role', 'owner')->where('status', 'active')->exists();
    }
}
