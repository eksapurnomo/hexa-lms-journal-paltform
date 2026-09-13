<?php

namespace App\Http\Resources;

use App\Models\Instructor;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InstructorResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {

        $instructor = Instructor::withCount('courses')
            ->withCount('reviews')  // Counts all reviews from courses
            ->withAvg('reviews', 'rating') // Averages review ratings
            ->withTrashed()
            ->find($this->id);

        if (!$instructor) {
            return [];
        }

        $totalEnrollments = Instructor::with('courses.enrollments')
            ->withTrashed()->find($this->id)
            ->courses->sum(function ($course) {
                return $course->enrollments->count();
            });

        return [
            'id' => $this->id,
            'name' => $this->user->name,
            'profile_picture' => $this->user->profilePicturePath,
            'title' => $this->title,
            'about' => $this->about,
            'is_featured' => $this->is_featured,
            'joining_date' => $this->created_at->format('d M, Y'),
            'average_rating' => number_format($instructor->reviews_avg_rating, 1) ?? 0,
            'reviews_count' => $instructor->reviews_count,
            'course_count' => $instructor->courses_count,
            'student_count' => $totalEnrollments,
            'experiences' => $this->experiences,
            'educations' => $this->educations,
        ];
    }
}
