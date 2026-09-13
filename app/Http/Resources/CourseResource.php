<?php

namespace App\Http\Resources;

use App\Enum\MediaTypeEnum;
use App\Repositories\ReviewRepository;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class CourseResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $contents = $this->chapters->flatMap(function ($chapter) {
            return $chapter->contents;
        });

        /** @var User */
        $loggedInUser = Auth::guard('api')->user();

        $isFavourite = $loggedInUser
            ? $this->favouriteUsers->contains($loggedInUser->id)
            : ($request->input('guest_id')
                ? $this->favouriteGuests->contains('unique_id', $request->input('guest_id'))
                : false);

        $isEnrolled = $loggedInUser
            ? $this->enrollments->contains('user_id', $loggedInUser->id)
            : false;


        $contentIds = $this->chapters->flatMap->contents->pluck('id')->toArray();
        $viewedContentIds = $loggedInUser?->viewedContents->pluck('id')->toArray() ?? [];

        $isCompleted = false;

        $progress = $loggedInUser ? $this->enrollments->where('user_id', $loggedInUser->id)->first() : '';

        if ($progress && $progress?->course_progress >= 100.00) {
            // $examSession = $this->exams->flatMap->examSessions->where('user_id', $loggedInUser->id)->where('submitted', true)->first();
            // $passMark = $this->exams->first()?->pass_marks ?? 0;

            // if ($examSession && $examSession->obtained_mark >= $passMark) {
            //     $isCompleted = true;
            // }
            $isCompleted = true;
        }


        // $isCompleted = empty(array_diff($contentIds, $viewedContentIds));

        $isReviewed = $loggedInUser
            ? $this->reviews->contains('user_id', $loggedInUser->id)
            : false;

        $canReview = $loggedInUser
            ? $isEnrolled && !$this->reviews->where('user_id', $loggedInUser->id)->where('course_id', $this->id)->first()
            : false;

        $submittedReview = $loggedInUser ? ReviewRepository::query()->where('user_id', $loggedInUser->id)->where('course_id', $this->id)->first() : null;


        return [
            'id' => $this->id,
            'category' => $this->category->title,
            'title' => $this->title,
            'thumbnail' => $this->mediaPath,
            'video' => $this->videoPath,
            'view_count' => $this->view_count,
            'regular_price' => $this->regular_price,
            'price' => $this->price,
            'instructor' => InstructorResource::make($this->instructor),
            'published_at' => $this->published_at,
            'total_duration' => $contents->sum('duration'),
            'video_count' => $contents->where('type', MediaTypeEnum::VIDEO)->count(),
            'free_video' => $contents->where('is_free', true)->where('type', MediaTypeEnum::VIDEO)->count(),
            'free_content' => $contents->where('is_free', true)->count(),
            'note_count' => $contents->where('type', MediaTypeEnum::DOCUMENT)->count(),
            'audio_count' => $contents->where('type', MediaTypeEnum::AUDIO)->count(),
            'chapter_count' => $this->chapters->count(),
            'student_count' => $this->enrollments->count(),
            'view_count' => $this->view_count,
            'review_count' => $this->reviews->count(),
            'average_rating' => number_format((float) $this->reviews->avg('rating'), 1) ?? (float) 0.0,
            'submitted_review' => ReviewResource::make($submittedReview),
            'is_favourite' => $isFavourite,
            'is_enrolled' => $isEnrolled,
            'is_completed' => $isCompleted,
            'is_reviewed' => $isReviewed,
            'can_review' => $canReview,
            'is_free' => $this->is_free,
            'media_link' => $this->media_link,
            'certificate_available' => $this->certificate_available,
            'progress' => $progress ?? 0,
        ];
    }
}
