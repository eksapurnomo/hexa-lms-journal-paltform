<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReviewStoreRequest;
use App\Http\Resources\ReviewResource;
use App\Models\Course;
use App\Repositories\EnrollmentRepository;
use App\Repositories\ReviewRepository;

class ReviewController extends Controller
{
    public function store(ReviewStoreRequest $request, Course $course)
    {
        $user = auth()->user();

        $isEnrolled = EnrollmentRepository::query()
            ->where('course_id', $course->id)
            ->where('user_id', $user->id)
            ->exists();

        if (!$isEnrolled) {
            return $this->json('Enrollment required', null, 403);
        }

        $alreadyReviewed = ReviewRepository::query()
            ->where('course_id', $course->id)
            ->where('user_id', $user->id)
            ->exists();

        if ($alreadyReviewed) {
            return $this->json('Already reviewed', null, 403);
        }

        $review = ReviewRepository::storeByRequest($request, $course);

        return $this->json('Review created successfully', [
            'review' => ReviewResource::make($review)
        ]);
    }
}
