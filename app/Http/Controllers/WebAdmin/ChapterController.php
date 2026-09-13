<?php

namespace App\Http\Controllers\WebAdmin;

use App\Enum\NotificationTypeEnum;
use App\Events\NotifyEvent;
use App\Http\Controllers\Controller;
use App\Http\Requests\ChapterStoreRequest;
use App\Http\Requests\ChapterUpdateRequest;
use App\Models\Chapter;
use App\Models\Course;
use App\Repositories\ChapterRepository;
use App\Repositories\CourseRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChapterController extends Controller
{
    public function selectCourse(Request $request)
    {
        $search = $request->cat_search ? strtolower($request->cat_search) : null;

        $user = auth()->user();

        $courses = CourseRepository::query()
            ->when(!$user->hasRole('admin'), function ($query) use ($user) {
                $query->where('instructor_id', $user->instructor?->id);
            })
            ->when($search, function ($query) use ($search) {
                $query->where('title', 'like', '%' . $search . '%');
            })
            ->latest('id')
            ->paginate(8)->withQueryString();

        return view('chapter.select_course', [
            'courses' => $courses,
        ]);
    }

    public function index(Request $request, Course $course)
    {
        $search = $request->cat_search ? strtolower($request->cat_search) : null;

        $chapters = ChapterRepository::query()->when($search, function ($query) use ($search) {
            $query->where('title', 'like', '%' . $search . '%');
        })->where('course_id', '=', $course->id)->latest('id')->paginate(8)->withQueryString();

        return view('chapter.index', [
            'chapters' => $chapters,
            'course' => $course
        ]);
    }

    public function create(Course $course)
    {
        $user = auth()->user();
        $courses = CourseRepository::query()
            ->when(!$user->hasRole('admin') || !$user->is_admin, function ($query) use ($user) {
                $query->where('instructor_id', $user->instructor?->id);
            })
            ->latest('id')
            ->get();

        return view('chapter.create', [
            'selectedCourse' => $course,
            'courses' => $courses,
        ]);
    }

    public function store(ChapterStoreRequest $request)
    {
        if (!auth()->user()->hasRole('admin') && !auth()->user()->is_admin) {
            $course = Course::findOrFail($request->course_id);
            abort_unless($course->instructor_id === auth()->user()->instructor->id, 403);
        }

        $chapter = ChapterRepository::storeByRequest($request);

        try {
            NotifyEvent::dispatch(NotificationTypeEnum::NewContentFromCourse->value, $chapter->course_id, [
                'course' => $chapter->course,
            ]);
        } catch (\Throwable $th) {
            //
        }

        return $this->json('Chapter created successfully', [
            'redirect' => route('chapter.index', ['course' => $chapter->course_id]),
            "chapter" => $chapter,
            'message' => 'Chapter created',
        ], 200);
    }

    public function edit(Chapter $chapter)
    {
        $this->authorize('update', $chapter);
        $user = auth()->user();
        $courses = CourseRepository::query()
            ->when(!$user->hasRole('admin') || !$user->is_admin, function ($query) use ($user) {
                $query->where('instructor_id', $user->instructor?->id);
            })
            ->latest('id')
            ->get();

        return view('chapter.edit', [
            'chapter' => $chapter,
            'courses' => $courses,
        ]);
    }

    public function update(ChapterUpdateRequest $request, Chapter $chapter)
    {
        $this->authorize('update', $chapter);

        $newContent = ChapterRepository::updateByRequest($request, $chapter);

        try {
            if ($newContent) {
                NotifyEvent::dispatch(NotificationTypeEnum::NewContentFromCourse->value, $chapter->course_id, [
                    'course' => $chapter->course
                ]);
            }
        } catch (\Throwable $th) {
            //
        }

        return $this->json('Chapter updated successfully', [
            'redirect' => route('chapter.index', ['course' => $chapter->course_id]),
            "chapter" => $chapter,
            'message' => 'Chapter updated',
        ], 200);
        // return to_route('chapter.index', ['course' => $chapter->course_id])->withSuccess('Chapter updated');
    }

    public function delete(Chapter $chapter)
    {
        $this->authorize('delete', $chapter);
        $courseId = $chapter->course_id;
        $chapter->delete();

        return redirect()->route('chapter.index', ['course' => $courseId])->withSuccess('Chapter deleted');
    }
}
