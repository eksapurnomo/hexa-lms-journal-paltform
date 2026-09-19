<?php

namespace App\Policies;

use App\Models\Submission;
use App\Models\User;

class SubmissionPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // Handled logically in the repository by scoping the query
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Submission $submission): bool
    {
        if ($user->hasRole('admin') || $user->is_admin) {
            return true;
        }

        // Author / Creator
        if ($submission->created_by === $user->id) {
            return true;
        }

        // Registered Co-Author
        if ($submission->authors()->where('user_id', $user->id)->exists()) {
            return true;
        }

        // Journal Owner or Assigned Editor
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
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Any registered user can create a submission
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Submission $submission): bool
    {
        if ($user->hasRole('admin') || $user->is_admin) {
            return true;
        }

        // Only Author can update, and ONLY if status is draft or revision_required
        return $submission->created_by === $user->id && in_array($submission->status, ['draft', 'revision_required']);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Submission $submission): bool
    {
        if ($user->hasRole('admin') || $user->is_admin) {
            return true;
        }

        // Only Author can delete, and ONLY if status is draft
        return $submission->created_by === $user->id && $submission->status === 'draft';
    }

    /**
     * Determine whether the user can submit the model.
     */
    public function submit(User $user, Submission $submission): bool
    {
        // Only Author can submit, and ONLY if status is draft
        return $submission->created_by === $user->id && $submission->status === 'draft';
    }

    /**
     * Determine whether the user can upload a file.
     */
    public function uploadFile(User $user, Submission $submission): bool
    {
        // Only Author can upload a file, and ONLY if status is draft or revision_required
        return $submission->created_by === $user->id && in_array($submission->status, ['draft', 'revision_required']);
    }

    /**
     * Determine whether the user can submit a revision.
     */
    public function submitRevision(User $user, Submission $submission): bool
    {
        // Only Author can submit a revision, and ONLY if status is revision_required
        return $submission->created_by === $user->id && $submission->status === 'revision_required';
    }

    /**
     * Determine whether the user can download a file.
     */
    public function downloadFile(User $user, Submission $submission): bool
    {
        // Same as view permission
        return $this->view($user, $submission);
    }
}
