<?php

namespace App\Http\Controllers\WebAdmin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Enrollment;
use App\Repositories\CourseRepository;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $filterType = $request->query('filter_type');
        $daterange = $request->query('daterange');

        $reportsQuery = CourseRepository::query()
            ->where('certificate_available', true)
            ->whereHas('instructor', function ($query) {
                return $query->where('user_id', auth()->id());
            })
            ->when($filterType !== 'all' && $filterType !== 'unpaid' && $filterType !== 'sale' && $daterange, function ($query) use ($daterange) {
                if ($daterange) {
                    [$startDate, $endDate] = explode('_', $daterange);
                    $startDate = Carbon::parse($startDate)->startOfDay();
                    $endDate = Carbon::parse($endDate)->endOfDay();

                    $query->whereHas('transactions', function ($query) use ($startDate, $endDate) {
                        return $query->whereBetween('created_at', [$startDate, $endDate]);
                    });
                }
            })

            ->when($filterType === 'sale' && $daterange, function ($query) use ($daterange) {
                if ($daterange) {
                    [$startDate, $endDate] = explode('_', $daterange);
                    $startDate = Carbon::parse($startDate)->startOfDay();
                    $endDate = Carbon::parse($endDate)->endOfDay();

                    $query->whereHas('transactions', function ($query) use ($startDate, $endDate) {
                        return $query->whereBetween('created_at', [$startDate, $endDate])
                            ->orderBy('payment_amount', 'desc');
                    });
                }
            })
            ->when($filterType === 'unpaid', function ($query) {
                $query->whereDoesntHave('enrollments')
                    ->orWhereHas('enrollments', function ($query) {
                        return $query->where('course_price', 0);
                    });
            })
            ->withTrashed()->paginate(10)->withQueryString();

        return view('report.index', [
            'reports' => $reportsQuery,
        ]);
    }

    public function filter(Request $request)
    {
        $filterType = $request->query('filter_type');
        $daterange = $request->query('daterange');


        $reportsQuery = CourseRepository::query()
            ->withCount('enrollments')
            ->where('certificate_available', true)
            ->whereHas('instructor', function ($query) {
                return $query->where('user_id', auth()->id());
            })
            ->when($filterType !== 'all' && $filterType !== 'unpaid' && $filterType !== 'sale' && $daterange, function ($query) use ($daterange) {
                if ($daterange) {
                    [$startDate, $endDate] = explode('_', $daterange);
                    $startDate = Carbon::parse($startDate)->startOfDay();
                    $endDate = Carbon::parse($endDate)->endOfDay();

                    $query->whereHas('transactions', function ($query) use ($startDate, $endDate) {
                        return $query->whereBetween('created_at', [$startDate, $endDate]);
                    });
                }
            })

            ->when($filterType === 'sale' && $daterange, function ($query) use ($daterange) {
                if ($daterange) {
                    [$startDate, $endDate] = explode('_', $daterange);
                    $startDate = Carbon::parse($startDate)->startOfDay();
                    $endDate = Carbon::parse($endDate)->endOfDay();

                    $query->whereHas('transactions', function ($query) use ($startDate, $endDate) {
                        return $query->whereBetween('created_at', [$startDate, $endDate])
                            ->orderBy('payment_amount', 'desc');
                    });
                }
            })
            ->when($filterType === 'unpaid', function ($query) {
                $query->whereDoesntHave('enrollments')
                    ->orWhereHas('enrollments', function ($query) {
                        return $query->where('course_price', 0);
                    });
            })
            ->when($filterType == 'all' && $daterange, function ($query) use ($daterange) {
                if ($daterange) {
                    [$startDate, $endDate] = explode('_', $daterange);
                    $startDate = Carbon::parse($startDate)->startOfDay();
                    $endDate = Carbon::parse($endDate)->endOfDay();
                    return $query->whereHas('transactions', function ($query) use ($startDate, $endDate) {
                        return $query->whereBetween('created_at', [$startDate, $endDate]);
                    });
                }
            })
            ->withTrashed()->paginate(10)->withQueryString();

        return view('report.index', [
            'reports' => $reportsQuery,
        ]);
    }



    public function generatePdf(Request $request)
    {
        $filterType = $request->query('filter_type');
        $daterange = $request->query('daterange');
        $page_num = $request->query('page', 1);

        $reportsQuery = CourseRepository::query()
            ->where('certificate_available', true)
            ->whereHas('instructor', function ($query) {
                return $query->where('user_id', auth()->id());
            })
            ->when($filterType != 'all' && $filterType != 'unpaid' && $daterange, function ($query) use ($daterange) {
                $query->whereHas('transactions', function ($query) use ($daterange) {
                    [$startDate, $endDate] = explode('-', $daterange);
                    return $query->whereDate('created_at', '>=', date('Y-m-d', strtotime($startDate)))
                        ->whereDate('created_at', '<=', date('Y-m-d', strtotime($endDate)));
                });
            })->when($filterType === 'sale', function ($query) {
                $query->whereHas('transactions', function ($query) {
                    return $query->orderBy('payment_amount', 'desc');
                });
            })->when($filterType === 'unpaid' && $daterange, function ($query) use ($daterange) {
                [$startDate, $endDate] = explode('-', $daterange);
                $query->whereHas('enrollments', function ($query) use ($startDate, $endDate) {
                    return $query->where('course_price', 0)->whereDate('created_at', '>=', date('Y-m-d', strtotime($startDate)))
                        ->whereDate('created_at', '<=', date('Y-m-d', strtotime($endDate)));
                });
            })
            ->withTrashed()->paginate(10, ['*'], 'page', $page_num);


        $pdf = Pdf::loadView('pdf.report', [
            'reports' => $reportsQuery->items(),
            'page_num' => $page_num,
        ]);

        return $pdf->stream("report-{$page_num}" . ".pdf");
    }

    public function exportCSV(Request $request)
    {
        $filterType = $request->query('filter_type');
        $daterange = $request->query('daterange');
        $page_num = $request->query('page', 1);

        $reportsQuery = CourseRepository::query()
            ->where('certificate_available', true)
            ->whereHas('instructor', function ($query) {
                return $query->where('user_id', auth()->id());
            })
            ->when($filterType != 'all' && $filterType != 'unpaid' && $daterange, function ($query) use ($daterange) {
                $query->whereHas('transactions', function ($query) use ($daterange) {
                    [$startDate, $endDate] = explode('-', $daterange);
                    return $query->whereDate('created_at', '>=', date('Y-m-d', strtotime($startDate)))
                        ->whereDate('created_at', '<=', date('Y-m-d', strtotime($endDate)));
                });
            })->when($filterType === 'sale', function ($query) {
                $query->whereHas('transactions', function ($query) {
                    return $query->orderBy('payment_amount', 'desc');
                });
            })->when($filterType === 'unpaid' && $daterange, function ($query) use ($daterange) {
                [$startDate, $endDate] = explode('-', $daterange);
                $query->whereHas('enrollments', function ($query) use ($startDate, $endDate) {
                    return $query->where('course_price', 0)->whereDate('created_at', '>=', date('Y-m-d', strtotime($startDate)))
                        ->whereDate('created_at', '<=', date('Y-m-d', strtotime($endDate)));
                });
            })
            ->withTrashed()->paginate(10, ['*'], 'page', $page_num);

        // Generate CSV content
        $csvContent = "SL,Date,Course,Category,Price,Total Enroll,Total Transactions,Grand Total\n";
        foreach ($reportsQuery as $index => $report) {
            $csvContent .= implode(',', [
                $index + 1,
                $report->created_at,
                $report->title,
                $report->category->title ?? 'N/A',
                $report->price,
                $report->enrollments->count(),
                $report->transactions->count(),
                $report->transactions->sum('payment_amount') ?? 'N/A',
            ]) . "\n";
        }

        // Send response as a CSV file
        return response($csvContent)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="report.csv"');
    }
}
