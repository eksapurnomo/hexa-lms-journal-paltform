<?php

namespace App\Policies;

use App\Models\Course;
use App\Models\User;

class CoursePolicy
{
    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Course $course): bool
    {
        if ($user->hasRole('admin') || $user->is_admin) {
            return true;
        }

        if ($user->instructor) {
            return $user->instructor->id === $course->instructor_id;
        }

        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Course $course): bool
    {
        if ($user->hasRole('admin') || $user->is_admin) {
            return true;
        }

        if ($user->instructor) {
            return $user->instructor->id === $course->instructor_id;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Course $course): bool
    {
        if ($user->hasRole('admin') || $user->is_admin) {
            return true;
        }

        if ($user->instructor) {
            return $user->instructor->id === $course->instructor_id;
        }

        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Course $course): bool
    {
        return $this->delete($user, $course);
    }
}
