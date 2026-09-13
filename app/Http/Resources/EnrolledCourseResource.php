<?php

namespace App\Http\Resources;

use App\Enum\MediaTypeEnum;
use App\Repositories\ReviewRepository;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class EnrolledCourseResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $course = $this->course;

        $contents = $course?->chapters?->flatMap(function ($chapter) {
            return $chapter?->contents;
        });

        /** @var User */
        $loggedInUser = Auth::guard('api')->user();

        $progress = $course?->enrollments->where('user_id', $loggedInUser->id)->first();

        $isFavourite = $loggedInUser
            ? $course?->favouriteUsers->contains($loggedInUser->id)
            : ($request->input('guest_id')
                ? $course?->favouriteGuests->contains('unique_id', $request->input('guest_id'))
                : false);

        $isEnrolled = $loggedInUser
            ? $course?->enrollments->contains('user_id', $loggedInUser->id)
            : false;

        $isReviewed = $loggedInUser
            ? $course?->reviews->contains('user_id', $loggedInUser->id)
            : false;

        $canReview = $loggedInUser
            ? $isEnrolled && !$course->reviews->where('user_id', $loggedInUser->id)->where('course_id', $course->id)->first()
            : false;

        $submittedReview = $loggedInUser ? ReviewRepository::query()->where('user_id', $loggedInUser->id)->where('course_id', $course->id)->first() : null;

        return [
            'id' => $course->id,
            'category' => $course->category->title,
            'title' => $course->title,
            'thumbnail' => $course->mediaPath,
            'view_count' => $course->view_count,
            'regular_price' => $course->regular_price,
            'price' => $course->price,
            'instructor' => InstructorResource::make($course->instructor),
            'published_at' => $course->published_at,
            'total_duration' => $contents->sum('duration'),
            'video_count' => $contents->where('type', MediaTypeEnum::VIDEO)->count(),
            'note_count' => $contents->where('type', MediaTypeEnum::DOCUMENT)->count(),
            'audio_count' => $contents->where('type', MediaTypeEnum::AUDIO)->count(),
            'chapter_count' => $course->chapters->count(),
            'student_count' => $course->enrollments->count(),
            'view_count' => $course->view_count,
            'review_count' => $course->reviews->count(),
            'average_rating' => (float) $course->reviews->avg('rating') ?? (float) 0.0,
            'submitted_review' => ReviewResource::make($submittedReview),
            'is_favourite' => $isFavourite,
            'is_enrolled' => $isEnrolled,
            'progress' => $progress?->course_progress ?? 0,
            'is_reviewed' => $isReviewed,
            'can_review' => $canReview,
            'enrolled_at' => $this->created_at,
            'last_activity' => $this->last_activity,
            'is_certificate_downloaded' => $this->is_certificate_downloaded,
        ];
    }
}
