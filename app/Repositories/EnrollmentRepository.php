<?php

namespace App\Repositories;

use Abedin\Maker\Repositories\Repository;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\User;
use App\Models\UserContentView;

class EnrollmentRepository extends Repository
{
    public static function model()
    {
        return Enrollment::class;
    }

    public static function updateProgress(Course $course, User $user)
    {
        $totalContents = $course->chapters->flatMap->contents->pluck('id')->unique()->count();

        $viewedContents = UserContentView::where('user_id', $user->id)
            ->whereIn('content_id', $course->chapters->flatMap->contents->pluck('id')->unique())
            ->pluck('content_id')
            ->unique()
            ->count();

        $progress = $totalContents > 0 ? min(($viewedContents / $totalContents) * 100, 100) : 0;

        $enrollment = EnrollmentRepository::query()
            ->where('course_id', '=', $course->id)
            ->where('user_id', '=', $user->id)
            ->first();
        // EnrollmentRepository::update($enrollment, ['course_progress' => round($progress, 2), 'last_activity' => now()]);
        if ($enrollment) {
            EnrollmentRepository::update($enrollment, [
                'course_progress' => round($progress, 2),
                'last_activity' => now()
            ]);
        }
    }
}
