<?php

namespace App\Http\Controllers\WebAdmin;

use App\Enum\NotificationTypeEnum;
use App\Events\NotifyEvent;
use App\Http\Controllers\Controller;
use App\Http\Requests\QuizStoreRequest;
use App\Http\Requests\QuizUpdateRequest;
use App\Models\Course;
use App\Models\Quiz;
use App\Repositories\CourseRepository;
use App\Repositories\QuizRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class QuizController extends Controller
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

        return view('quiz.select_course', [
            'courses' => $courses,
        ]);
    }

    public function index(Request $request, Course $course)
    {
        $search = $request->cat_search ? strtolower($request->cat_search) : null;

        $quizzes = QuizRepository::query()->when($search, function ($query) use ($search) {
            $query->where('title', 'like', '%' . $search . '%');
        })
            ->where('course_id', '=', $course->id)
            ->latest('id')
            ->paginate(8)->withQueryString();

        return view('quiz.index', [
            'quizzes' => $quizzes,
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

        return view('quiz.create', [
            'selectedCourse' => $course,
            'courses' => $courses,
        ]);
    }

    public function store(QuizStoreRequest $request)
    {
        if (!auth()->user()->hasRole('admin') && !auth()->user()->is_admin) {
            $course = Course::findOrFail($request->course_id);
            abort_unless($course->instructor_id === auth()->user()->instructor->id, 403);
        }

        $quiz = QuizRepository::storeByRequest($request);

        NotifyEvent::dispatch(NotificationTypeEnum::NewQuizFromCourse->value, $quiz->course_id, [
            'course' => $quiz->course
        ]);

        return to_route('quiz.index', ['course' => $quiz->course_id])->with('success', 'Quiz created');
    }

    public function edit(Quiz $quiz)
    {
        $this->authorize('update', $quiz);
        $user = auth()->user();
        $courses = CourseRepository::query()
            ->when(!$user->hasRole('admin') || !$user->is_admin, function ($query) use ($user) {
                $query->where('instructor_id', $user->instructor?->id);
            })
            ->latest('id')
            ->get();

        return view('quiz.edit', [
            'quiz' => $quiz,
            'courses' => $courses,
        ]);
    }

    public function update(QuizUpdateRequest $request, Quiz $quiz)
    {
        $this->authorize('update', $quiz);
        QuizRepository::updateByRequest($request, $quiz);

        return to_route('quiz.index', ['course' => $quiz->course_id])->withSuccess('Quiz updated');
    }

    public function delete(Quiz $quiz)
    {
        $this->authorize('delete', $quiz);
        $courseId = $quiz->course_id;
        $quiz->delete();

        return redirect()->route('quiz.index', ['course' => $courseId])->withSuccess('Quiz deleted');
    }
}
