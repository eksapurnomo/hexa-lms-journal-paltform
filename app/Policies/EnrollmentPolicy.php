<?php

namespace App\Policies;

use App\Models\Enrollment;
use App\Models\User;

class EnrollmentPolicy
{
    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Enrollment $enrollment): bool
    {
        if ($user->hasRole('admin') || $user->is_admin) {
            return true;
        }

        if ($user->instructor && $enrollment->course) {
            return $user->instructor->id === $enrollment->course->instructor_id;
        }

        return false;
    }
}
