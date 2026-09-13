<?php

namespace App\Http\Controllers\WebAdmin;

use App\Enum\NotificationTypeEnum;
use App\Events\NotifyEvent;
use App\Http\Controllers\Controller;
use App\Http\Requests\ExamStoreRequest;
use App\Http\Requests\ExamUpdateRequest;
use App\Models\Course;
use App\Models\Exam;
use App\Repositories\CourseRepository;
use App\Repositories\ExamRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ExamController extends Controller
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
                $query->where('title', 'like', '%' . $search . '%')
                    ->orWhereHas('category', function ($query) use ($search) {
                        $query->where('title', 'like', '%' . $search . '%');
                    });
            })
            ->latest('id')
            ->paginate(8)->withQueryString();

        return view('exam.select_course', [
            'courses' => $courses,
        ]);
    }

    public function index(Request $request, Course $course)
    {
        $search = $request->cat_search ? strtolower($request->cat_search) : null;

        $exams = ExamRepository::query()->when($search, function ($query) use ($search) {
            $query->where('title', 'like', '%' . $search . '%');
        })
            ->where('course_id', '=', $course->id)
            ->latest('id')
            ->paginate(8)->withQueryString();

        return view('exam.index', [
            'exams' => $exams,
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

        return view('exam.create', [
            'selectedCourse' => $course,
            'courses' => $courses,
        ]);
    }

    public function store(ExamStoreRequest $request)
    {
        if (!auth()->user()->hasRole('admin') && !auth()->user()->is_admin) {
            $course = Course::findOrFail($request->course_id);
            abort_unless($course->instructor_id === auth()->user()->instructor->id, 403);
        }

        $exam = ExamRepository::storeByRequest($request);

        NotifyEvent::dispatch(NotificationTypeEnum::NewExamFromCourse->value, $exam->course_id, [
            'course' => $exam->course,
        ]);

        return to_route('exam.index', ['course' => $exam->course_id])->with('success', 'Exam created');
    }

    public function edit(Exam $exam)
    {
        $this->authorize('update', $exam);

        $user = auth()->user();
        $courses = CourseRepository::query()
            ->when(!$user->hasRole('admin') || !$user->is_admin, function ($query) use ($user) {
                $query->where('instructor_id', $user->instructor?->id);
            })
            ->latest('id')
            ->get();


        return view('exam.edit', [
            'exam' => $exam,
            'courses' => $courses,
        ]);
    }

    public function update(ExamUpdateRequest $request, Exam $exam)
    {
        $this->authorize('update', $exam);
        ExamRepository::updateByRequest($request, $exam);

        return to_route('exam.index', ['course' => $exam->course_id])->withSuccess('Exam updated');
    }

    public function delete(Exam $exam)
    {
        $this->authorize('delete', $exam);
        $courseId = $exam->course_id;
        $exam->delete();

        return redirect()->route('exam.index', ['course' => $courseId])->withSuccess('Exam deleted');
    }
}
