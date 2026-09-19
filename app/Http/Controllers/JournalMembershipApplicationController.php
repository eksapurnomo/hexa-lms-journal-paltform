<?php

namespace App\Http\Controllers;

use App\Models\AcademicProfile;
use App\Models\Institution;
use App\Models\Journal;
use App\Models\JournalMembershipApplication;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class JournalMembershipApplicationController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $applications = JournalMembershipApplication::where('user_id', $user->id)
            ->with('journal')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('journal-membership-application.index', compact('applications'));
    }

    public function create()
    {
        $user = Auth::user();
        $journals = Journal::where('status', 'active')->get();
        $institutions = Institution::where('is_active', true)->get();
        
        $profile = AcademicProfile::firstOrCreate(['user_id' => $user->id]);

        return view('journal-membership-application.create', compact('journals', 'institutions', 'profile'));
    }

    public function store(Request $request, \App\Services\JournalMembershipApplicationService $service)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'journal_id' => 'required|exists:journals,id',
            'requested_role' => 'required|in:owner,editor,reviewer',
            
            // Academic Profile Data
            'academic_type' => 'required|in:lecturer,researcher',
            'highest_degree' => 'nullable|string|max:255',
            'academic_position' => 'nullable|string|max:255',
            'institution_type' => 'nullable|string|max:255|in:university,research_institute,government_research,ngo,private_research,think_tank,other,independent',
            'institution_id' => 'nullable|exists:institutions,id',
            'institution' => 'nullable|string|max:255',
            'department' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:255',
            'biography' => 'nullable|string',
            'research_interests' => 'nullable|string',
            'institutional_email' => 'nullable|email|max:255',
            'orcid' => 'nullable|string|max:255',
            'sinta_id' => 'nullable|string|max:255',
            'scopus_author_id' => 'nullable|string|max:255',
            'google_scholar_url' => 'nullable|url|max:255',
        ]);

        try {
            $app = $service->createDraft($user, $validated);

            return redirect()->route('membership-applications.show', $app->id)
                ->with('success', 'Application drafted. Please upload verification evidence and submit.');
        } catch (\Exception $e) {
            return redirect()->route('membership-applications.index')
                ->with('error', $e->getMessage());
        }
    }

    public function show($id)
    {
        $user = Auth::user();
        $application = JournalMembershipApplication::where('user_id', $user->id)
            ->with(['journal', 'verificationEvidences'])
            ->findOrFail($id);

        $profile = AcademicProfile::where('user_id', $user->id)->first();

        return view('journal-membership-application.show', compact('application', 'profile'));
    }

    public function edit($id)
    {
        $user = Auth::user();
        $application = JournalMembershipApplication::where('user_id', $user->id)
            ->findOrFail($id);

        if (!in_array($application->status, [JournalMembershipApplication::STATUS_DRAFT, JournalMembershipApplication::STATUS_NEEDS_REVISION])) {
            return redirect()->route('membership-applications.show', $id)
                ->with('error', 'Application cannot be edited in its current state.');
        }

        $journals = Journal::where('status', 'active')->get();
        $institutions = Institution::where('is_active', true)->get();
        $profile = AcademicProfile::firstOrCreate(['user_id' => $user->id]);

        return view('journal-membership-application.edit', compact('application', 'journals', 'institutions', 'profile'));
    }

    public function update(Request $request, $id, \App\Services\JournalMembershipApplicationService $service)
    {
        $user = Auth::user();
        $application = JournalMembershipApplication::where('user_id', $user->id)
            ->findOrFail($id);

        $validated = $request->validate([
            // Academic Profile Data (same as store, excluding journal and role)
            'academic_type' => 'required|in:lecturer,researcher',
            'highest_degree' => 'nullable|string|max:255',
            'academic_position' => 'nullable|string|max:255',
            'institution_type' => 'nullable|string|max:255|in:university,research_institute,government_research,ngo,private_research,think_tank,other,independent',
            'institution_id' => 'nullable|exists:institutions,id',
            'institution' => 'nullable|string|max:255',
            'department' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:255',
            'biography' => 'nullable|string',
            'research_interests' => 'nullable|string',
            'institutional_email' => 'nullable|email|max:255',
            'orcid' => 'nullable|string|max:255',
            'sinta_id' => 'nullable|string|max:255',
            'scopus_author_id' => 'nullable|string|max:255',
            'google_scholar_url' => 'nullable|url|max:255',
        ]);

        try {
            $service->updateDraft($user, $application, $validated);

            return redirect()->route('membership-applications.show', $application->id)
                ->with('success', 'Application profile updated.');
        } catch (\Exception $e) {
            return redirect()->route('membership-applications.show', $id)
                ->with('error', $e->getMessage());
        }
    }

    public function submit(Request $request, $id, \App\Services\JournalMembershipApplicationService $service)
    {
        $user = Auth::user();
        $application = JournalMembershipApplication::where('user_id', $user->id)
            ->findOrFail($id);

        try {
            $service->submitApplication($user, $application);
            return back()->with('success', 'Application submitted successfully for review.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
