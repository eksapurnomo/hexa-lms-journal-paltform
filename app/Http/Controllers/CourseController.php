<?php

namespace App\Http\Controllers;

use App\Events\CustomNotifyEvent;
use App\Events\NotifyEvent;
use App\Http\Resources\ChapterResource;
use App\Http\Resources\ContentResource;
use App\Http\Resources\CourseDescriptionResource;
use App\Http\Resources\CourseResource;
use App\Http\Resources\ExamResource;
use App\Http\Resources\QuizResource;
use App\Http\Resources\ReviewResource;
use App\Models\Chapter;
use App\Models\Content;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\User;
use App\Repositories\CourseRepository;
use App\Repositories\EnrollmentRepository;
use App\Repositories\UserContentViewRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CourseController extends Controller
{
    public function index(Request $request)
    {
        $query = CourseRepository::query()
            ->where('is_active', true)
            ->withAvg('reviews as average_rating', 'rating');

        // Filter
        $filterableFields = ['category_id', 'price', 'instructor_id'];
        foreach ($filterableFields as $field) {
            if ($request->has($field)) {
                $value = $request->input($field);

                // If the field is category_id and the value is an array, use whereIn
                if (($field === 'category_id' || $field === 'instructor_id') && is_array($value)) {
                    $query->whereIn($field, $value);
                } else {
                    $query->where($field, $value);
                }
            }
        }

        // Average rating filter
        $averageRating = $request->input('average_rating');
        $floor = floor($averageRating);
        $ceil = $floor + 0.9;

        $query->when($averageRating, function ($reviewQuery) use ($floor, $ceil) {
            $reviewQuery->havingRaw('average_rating between ? and ?', [$floor, $ceil]);
        });

        // Price filter
        $priceFrom = $request->input('price_from');
        $priceTo = $request->input('price_to');

        if ($priceFrom !== null) {
            $query->where('price', '>=', $priceFrom);
        }

        if ($priceTo !== null) {
            $query->where('price', '<=', $priceTo);
        }

        // Sorting logic
        $sortableFields = ['view_count', 'price', 'published_at', 'average_rating', 'total_duration'];
        $sortField = $request->input('sort');
        $sortOrder = $request->input('sortDirection') ?? 'desc';

        if (in_array($sortField, $sortableFields) && in_array($sortOrder, ['asc', 'desc'])) {
            $query->orderBy($sortField, $sortOrder);
        }

        // Total duration calculation and sorting (i comment this because i need this when this feature came)
        $subquery = CourseRepository::query()
            ->selectRaw('courses.id')
            ->leftJoin('chapters', 'courses.id', '=', 'chapters.course_id')
            ->leftJoin('contents', 'chapters.id', '=', 'contents.chapter_id')
            ->groupBy('courses.id')
            ->selectRaw('COALESCE(SUM(contents.duration), 0) as total_duration');

        $query->addSelect(['courses.*', 'subquery.total_duration'])
            ->leftJoinSub($subquery, 'subquery', 'courses.id', '=', 'subquery.id')
            ->orderBy('subquery.total_duration', $sortOrder);


        // Search
        if ($request->has('search')) {
            $searchTerm = $request->input('search');
            $query->where('title', 'like', '%' . $searchTerm . '%');
        }

        // Pagination


        $totalItems = $query->whereHas('instructor', function ($query) {
            $query->whereNull('deleted_at');
        })->count();
        $perPage = $request->input('items_per_page', 15);
        $pageNumber = $request->input('page_number', 1);
        $skip = ($pageNumber - 1) * $perPage;
        $courses = $query->whereHas('instructor', function ($query) {
            $query->whereNull('deleted_at');
        })
            ->skip($skip)
            ->take($perPage)
            ->get();

        return $this->json($courses ? 'Courses found' : 'No courses found', [
            'total_courses' => $totalItems,
            'courses' => CourseResource::collection($courses),
        ], $courses ? 200 : 404);
    }

    public function show(Course $course)
    {
        if (!$course->is_active) {
            return $this->json('Course not found', null, 404);
        }

        // Increment course view per visit
        $course->update([
            'view_count' => $course->view_count + 1
        ]);

        return $this->json($course ? 'Course found' : 'Course not found',  !$course ? null : [
            'course' => CourseResource::make($course),
            'description' => CourseDescriptionResource::collection(collect(json_decode($course->description))),
            'chapters' => ChapterResource::collection($course->chapters),
            'quizzes' => QuizResource::collection($course->quizzes),
            'exams' => ExamResource::collection($course->exams),
            'reviews' => ReviewResource::collection($course->reviews),
        ], $course ? 200 : 404);
    }

    public function viewContent(Content $content)
    {
        /** @var User */
        $loggedInUser = auth()->user();

        if ($content->is_free || $content->chapter->course->is_free) {
            UserContentViewRepository::create([
                'user_id' => $loggedInUser->id,
                'content_id' => $content->id
            ]);
            EnrollmentRepository::updateProgress($content->chapter->course, $loggedInUser);
        }

        if (!$content->is_free) {
            if (!$content->chapter->course->is_free) {
                $isEnrolled = $loggedInUser ? $content->chapter->course->enrollments->contains('user_id', $loggedInUser->id) : false;

                if (!$isEnrolled) {
                    return $this->json('Enrollment required', null, 403);
                }

                UserContentViewRepository::create([
                    'user_id' => $loggedInUser->id,
                    'content_id' => $content->id
                ]);

                EnrollmentRepository::updateProgress($content->chapter->course, $loggedInUser);
            }
        }

        return $this->json('Content viewed', ['content' => ContentResource::make($content)], 200);
    }

    public function getProgress(Request $request, Course $course)
    {
        $user = Auth::guard('api')->user();

        $progress = $course->userProgress()->wherePivot('user_id',  $user->id)->first();

        return $this->json('Course Progress Track', [
            'progress' => $progress->pivot->progress
        ], 200);
    }

    public function progress(Request $request, Course $course)
    {
        /** @var User $user */
        $user = Auth::guard('api')->user();

        $progress = $user?->courseProgresses()?->wherePivot('course_id', $course->id)->first();

        if (!$progress) {
            $user->courseProgresses()->attach($course->id, ['progress' => $request->progress]);
        } else if ($progress && $progress?->pivot->progress < $request->progress) {
            $user->courseProgresses()?->updateExistingPivot($course->id, ['progress' => $request->progress]);
        }

        $enrollment = Enrollment::where('course_id', $course->id)->where('user_id', $user->id)->first();

        if ($enrollment) {
            $enrollment->update([
                'course_progress' => $request->progress,
            ]);
        }

        return $this->json('Course Track', ['progress' => $request->progress], 200);
    }
}
