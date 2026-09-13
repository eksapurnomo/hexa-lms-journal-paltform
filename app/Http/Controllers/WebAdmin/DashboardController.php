<?php

namespace App\Http\Controllers\WebAdmin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Instructor;
use App\Models\Transaction;
use App\Models\User;
use App\Repositories\CourseRepository;
use App\Repositories\EnrollmentRepository;
use App\Repositories\InstructorRepository;
use App\Repositories\ReviewRepository;
use App\Repositories\TransactionRepository;
use App\Repositories\UserRepository;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class DashboardController extends Controller
{
    public function index()
    {

        $topStudents = UserRepository::query()->where('is_admin', false)->withCount('enrollments')->orderBy('enrollments_count', 'desc')->limit(5)->get();

        $topInstructors = InstructorRepository::query()
            ->with(['user'])
            ->withCount('courses')
            ->withCount('reviews')
            ->withAvg('reviews', 'rating')
            ->orderBy('courses_count', 'desc')
            ->limit(5)
            ->get();

        return view('dashboard.index', [
            'active_course_count' => CourseRepository::query()->where('is_active', true)->count(),
            'enrollment_count' => EnrollmentRepository::getAll()->count(),
            'total_users_count' => UserRepository::getAll()->count(),
            'student_count' => UserRepository::query()->where('is_admin', false)->count(),
            'instructor_count' => InstructorRepository::getAll()->count(),
            'review_count' => ReviewRepository::getAll()->count(),
            'transaction_amount' => TransactionRepository::query()->where('is_paid', true)->sum('payable_amount'),
            'popular_courses' => CourseRepository::query()->orderBy('view_count', 'desc')->limit(5)->get(),
            'topStudents' => $topStudents,
            'topInstructors' => $topInstructors,
            'transaction_count' => Transaction::query()->where('is_paid', true)->count(),
        ]);
    }

    public function statistics(Request $request)
    {

        try {
            $user = UserRepository::find($request->author_id);
            //Ensure the user is authenticated
            $instructorId = $user?->instructor?->id;
            $type = $request->type;

            // Check if 'type' is provided
            if (!in_array($type, ['daily', 'monthly', 'yearly'])) {
                return response()->json([
                    'message' => 'Invalid type provided.',
                ], 400);
            }

            if ($type == 'daily') {
                $startDate = now()->startOfWeek();
                $endDate = now()->endOfWeek();

                $datesAndDays = [];
                for ($date = $startDate; $date->lte($endDate); $date->addDay()) {
                    if ($user->hasRole('admin')) {
                        $datesAndDays[] = [
                            'date' => $date->toDateString(),
                            'label' => $date->format('l'),
                            'value' => Transaction::whereDate('created_at', $date->toDateString())
                                ->where('is_paid', true)
                                ->count(),
                        ];
                    } else {
                        $datesAndDays[] = [
                            'date' => $date->toDateString(),
                            'label' => $date->format('l'),
                            'value' => Transaction::whereDate('created_at', $date->toDateString())
                                ->where('is_paid', true)
                                ->whereHas('course', function ($query) use ($instructorId) {
                                    $query->where('instructor_id', $instructorId);
                                })
                                ->count(),
                        ];
                    }
                }

                $labels = array_column($datesAndDays, 'label');
                $values = array_column($datesAndDays, 'value');

                return response()->json([
                    'message' => 'Daily order statistics',
                    'data' => [
                        'labels' => $labels,
                        'values' => $values,
                        'total' => array_sum($values),
                    ],
                ]);
            } elseif ($type == 'monthly') {
                $startDate = now()->startOfYear();
                $endDate = now()->endOfYear();

                $datesAndMonths = [];
                for ($date = $startDate; $date->lte($endDate); $date->addMonth()) {
                    if ($user->hasRole('admin')) {
                        $datesAndMonths[] = [
                            'date' => $date->toDateString(),
                            'label' => $date->format('M'),
                            'value' => Transaction::whereMonth('created_at', $date->month)
                                ->where('is_paid', true)
                                ->whereYear('created_at', $date->year)
                                ->count(),
                        ];
                    } else {
                        $datesAndMonths[] = [
                            'date' => $date->toDateString(),
                            'label' => $date->format('M'),
                            'value' => Transaction::whereMonth('created_at', $date->month)
                                ->where('is_paid', true)
                                ->whereYear('created_at', $date->year)
                                ->whereHas('course', function ($query) use ($instructorId) {
                                    $query->where('instructor_id', $instructorId);
                                })
                                ->count(),
                        ];
                    }
                }

                $labels = array_column($datesAndMonths, 'label');
                $values = array_column($datesAndMonths, 'value');

                return response()->json([
                    'message' => 'Monthly order statistics',
                    'data' => [
                        'labels' => $labels,
                        'values' => $values,
                        'total' => array_sum($values),
                    ],
                ]);
            } else {
                $datesAndYears = [];
                for ($date = now()->startOfYear(); $date->year >= now()->subYears(6)->year; $date->subYear()) {
                    if ($user->hasRole('admin')) {
                        $datesAndYears[] = [
                            'date' => $date->toDateString(),
                            'label' => $date->format('Y'),
                            'value' => Transaction::whereYear('created_at', $date->year)
                                ->where('is_paid', true)
                                ->count(),
                        ];
                    } else {

                        $datesAndYears[] = [
                            'date' => $date->toDateString(),
                            'label' => $date->format('Y'),
                            'value' => Transaction::whereYear('created_at', $date->year)
                                ->where('is_paid', true)
                                ->whereHas('course', function ($query) use ($instructorId) {
                                    $query->where('instructor_id', $instructorId);
                                })
                                ->count(),
                        ];
                    }
                }

                $labels = array_column($datesAndYears, 'label');
                $values = array_column($datesAndYears, 'value');

                return response()->json([
                    'message' => 'Yearly order statistics',
                    'data' => [
                        'labels' => $labels,
                        'values' => $values,
                        'total' => array_sum($values),
                    ],
                ]);
            }
        } catch (\Exception $e) {
            // Log the exception details
            Log::error('Error fetching statistics', ['error' => $e->getMessage(), 'stack' => $e->getTraceAsString()]);
            return response()->json([
                'message' => 'An error occurred while fetching statistics.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    public function instructorHome()
    {
        $totalTransactionsAmount = TransactionRepository::query()->whereHas('course', function ($query) {
            $query->where('instructor_id', Auth::user()?->instructor?->id);
        })
            ->where('is_paid', true)
            ->sum('payable_amount');

        $activeCourseCount = CourseRepository::query()->where('instructor_id', Auth::user()?->instructor?->id)->where('is_active', true)->count();
        $totalEnrollmentsCount = CourseRepository::query()->whereHas('instructor', function ($query) {
            $query->where('user_id', Auth::user()?->id);
        })->whereHas('enrollments')->count();
        $topSaleCourse = CourseRepository::query()
            ->withTrashed()
            ->where('instructor_id', Auth::user()?->instructor?->id)
            ->whereHas('transactions', function ($query) {
                $query->where('is_paid', true);
            })
            ->withSum(['transactions as total_payment' => function ($query) {
                $query->where('is_paid', true);
            }], 'payment_amount')
            ->orderBy('total_payment', 'desc')
            ->limit(5)
            ->get();

        $totalReview = ReviewRepository::query()->whereHas('course', function ($query) {
            $query->where('instructor_id', Auth::user()?->instructor?->id);
        })->count();

        $instructorId = auth()->user()?->instructor?->id;

        $courseId = CourseRepository::query()->where('instructor_id', $instructorId)->pluck('id')->toArray();
        $studentCounts = UserRepository::query()->whereHas('enrollments', function ($query) use ($courseId) {
            $query->whereIn('course_id', $courseId);
        })->count();


        return view('dashboard.instructor', [
            'topSaleCourse' => $topSaleCourse,
            'activeCourseCount' => $activeCourseCount,
            'totalEnrollmentsCount' => $totalEnrollmentsCount,
            'student_count' => $studentCounts,
            'instructor_count' => InstructorRepository::getAll()->count(),
            'totalTransactionsAmount' => $totalTransactionsAmount,
            'totalReview' => $totalReview,
        ]);
    }
}
