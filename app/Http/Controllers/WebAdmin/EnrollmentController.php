<?php

namespace App\Http\Controllers\WebAdmin;

use App\Http\Controllers\Controller;
use App\Models\Enrollment;
use App\Repositories\EnrollmentRepository;
use Illuminate\Http\Request;

class EnrollmentController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->cat_search ? strtolower($request->cat_search) : null;


        if (auth()->user()->hasRole('admin') || auth()->user()->is_admin) {
            $enrollments = EnrollmentRepository::query()
                ->when($search, function ($query) use ($search) {
                    $query->whereHas('course', function ($query) use ($search) {
                        $query->where('title', 'like', '%' . $search . '%');
                    })
                        ->orWhereHas('user', function ($query) use ($search) {
                            $query->where('name', 'like', '%' . $search . '%');
                        });
                })
                ->withTrashed()
                ->latest('id')
                ->paginate(15)->withQueryString();
        } else {
            $enrollments = EnrollmentRepository::query()
                ->whereHas('course', function ($query) {
                    $query->whereHas('instructor', function ($query) {
                        $query->whereHas('user', function ($query) {
                            $query->where('user_id', auth()->user()->id);
                        });
                    });
                })
                ->when($search, function ($query) use ($search) {
                    $query->whereHas('course', function ($query) use ($search) {
                        $query->where('title', 'like', '%' . $search . '%');
                    })
                        ->orWhereHas('user', function ($query) use ($search) {
                            $query->where('name', 'like', '%' . $search . '%');
                        });
                })
                ->withTrashed()
                ->latest('id')
                ->paginate(15)->withQueryString();
        }


        return view('enrollment.index', [
            'enrollments' => $enrollments,
        ]);
    }

    public function delete(Enrollment $enrollment)
    {
        $this->authorize('delete', $enrollment);
        $enrollment->course_progress = 0.00;
        $enrollment->save();
        $enrollment->delete();
        return redirect()->route('enrollment.index')->withSuccess('Enrollment Canceled');
    }
    public function suspended(Enrollment $enrollment)
    {
        $this->authorize('delete', $enrollment);
        $enrollment->delete();
        return redirect()->route('enrollment.index')->withSuccess('Enrollment Successfully Suspended');
    }

    public function restore(int $id)
    {
        $enrollment = EnrollmentRepository::query()->onlyTrashed()->findOrFail($id);
        $this->authorize('delete', $enrollment);
        $enrollment->restore();

        return redirect()->route('enrollment.index')->withSuccess('Enrollment restored');
    }
}
