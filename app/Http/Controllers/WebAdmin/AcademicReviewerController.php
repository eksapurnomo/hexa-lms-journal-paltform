<?php

namespace App\Http\Controllers\WebAdmin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Journal;
use Illuminate\Http\Request;

class AcademicReviewerController extends Controller
{
    public function index(Request $request)
    {
        // Enforce platform-admin level access using existing semantics
        abort_if(!auth()->user()->is_admin && !auth()->user()->hasRole('admin'), 403, 'Unauthorized.');

        $search = $request->input('search');
        $journalId = $request->input('journal_id');
        $role = $request->input('role');

        $users = User::query()
            ->with(['academicProfile', 'journalMemberships.journal'])
            ->where(function ($q) {
                $q->has('academicProfile')
                  ->orHas('journalMemberships');
            })
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', '%' . $search . '%')
                      ->orWhere('email', 'like', '%' . $search . '%');
                });
            })
            ->when($journalId, function ($query) use ($journalId) {
                $query->whereHas('journalMemberships', function ($q) use ($journalId) {
                    $q->where('journal_id', $journalId);
                });
            })
            ->when($role, function ($query) use ($role) {
                $query->whereHas('journalMemberships', function ($q) use ($role) {
                    $q->where('role', $role);
                });
            })
            ->latest('id')
            ->paginate(25)
            ->withQueryString();

        $journals = Journal::orderBy('title')->get();

        return view('academic-reviewer.index', compact('users', 'journals'));
    }

    public function show(User $user)
    {
        // Enforce platform-admin level access using existing semantics
        abort_if(!auth()->user()->is_admin && !auth()->user()->hasRole('admin'), 403, 'Unauthorized.');

        $user->load(['academicProfile', 'journalMemberships.journal']);

        return view('academic-reviewer.show', compact('user'));
    }
}
