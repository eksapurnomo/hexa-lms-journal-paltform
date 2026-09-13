<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        \App\Models\Course::class => \App\Policies\CoursePolicy::class,
        \App\Models\Chapter::class => \App\Policies\ChapterPolicy::class,
        \App\Models\Quiz::class => \App\Policies\QuizPolicy::class,
        \App\Models\Exam::class => \App\Policies\ExamPolicy::class,
        \App\Models\Enrollment::class => \App\Policies\EnrollmentPolicy::class,
        \App\Models\Journal::class => \App\Policies\JournalPolicy::class,
        \App\Models\Submission::class => \App\Policies\SubmissionPolicy::class,
        \App\Models\ReviewAssignment::class => \App\Policies\ReviewAssignmentPolicy::class,
        \App\Models\PeerReview::class => \App\Policies\PeerReviewPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        // Super-admin bypass: is_admin=true users pass all Gate/permission checks.
        // This is the Spatie-recommended pattern for super-administrators who are
        // identified by the is_admin flag rather than a Spatie role.
        \Illuminate\Support\Facades\Gate::before(function (\App\Models\User $user, string $ability) {
            if ($user->is_admin) {
                return true;
            }
        });

        \Illuminate\Support\Facades\Gate::define('editorial-process', [\App\Policies\EditorialPolicy::class, 'process']);
        \Illuminate\Support\Facades\Gate::define('editorial-assign', [\App\Policies\EditorialPolicy::class, 'assign']);
    }
}
