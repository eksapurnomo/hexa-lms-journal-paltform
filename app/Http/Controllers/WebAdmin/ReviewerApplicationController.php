<?php

namespace App\Http\Controllers\WebAdmin;

use App\Http\Controllers\Controller;
use App\Models\ReviewerApplication;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReviewerApplicationController extends Controller
{
    /**
     * Display a listing of the reviewer applications.
     */
    public function index(Request $request)
    {
        $query = ReviewerApplication::with(['user', 'journal'])->latest();

        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        $applications = $query->paginate(15);
        $currentStatus = $request->status ?? 'all';

        return view('admin.reviewer_applications.index', compact('applications', 'currentStatus'));
    }

    /**
     * Display the specified reviewer application.
     */
    public function show($id)
    {
        $application = ReviewerApplication::with(['user', 'journal', 'reviewedBy'])->findOrFail($id);
        
        return view('admin.reviewer_applications.show', compact('application'));
    }

    /**
     * Accept the reviewer application.
     */
    public function accept($id)
    {
        $application = ReviewerApplication::findOrFail($id);

        if ($application->status !== ReviewerApplication::STATUS_PENDING) {
            return back()->with('error', 'Only pending applications can be accepted.');
        }

        $application->update([
            'status' => ReviewerApplication::STATUS_ACCEPTED,
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
        ]);

        return redirect()->route('admin.reviewer_applications.show', $application->id)
            ->with('success', 'Application accepted successfully.');
    }

    /**
     * Deny the reviewer application.
     */
    public function deny(Request $request, $id)
    {
        $application = ReviewerApplication::findOrFail($id);

        if ($application->status !== ReviewerApplication::STATUS_PENDING) {
            return back()->with('error', 'Only pending applications can be denied.');
        }

        $request->validate([
            'denial_reason' => 'required|string|max:2000',
        ]);

        $application->update([
            'status' => ReviewerApplication::STATUS_DENIED,
            'denial_reason' => $request->denial_reason,
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
        ]);

        return redirect()->route('admin.reviewer_applications.show', $application->id)
            ->with('success', 'Application denied successfully.');
    }
}
