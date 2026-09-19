<?php

namespace App\Http\Controllers;

use App\Models\Journal;
use App\Models\ReviewerApplication;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReviewerApplicationController extends Controller
{
    /**
     * Show the application status or redirect to form.
     */
    public function show(Request $request)
    {
        $user = Auth::user();
        
        // Find if user has any pending or accepted applications
        $application = ReviewerApplication::where('user_id', $user->id)
            ->whereIn('status', [ReviewerApplication::STATUS_PENDING, ReviewerApplication::STATUS_ACCEPTED])
            ->first();

        if (!$application) {
            // Also check for a denied one if we want to show it, but we can just get the latest application
            $application = ReviewerApplication::where('user_id', $user->id)->latest()->first();
        }

        if (!$application) {
            return redirect()->route('reviewer.apply');
        }

        return view('journal.reviewer.status', compact('application'));
    }

    /**
     * Show the application form.
     */
    public function create()
    {
        $user = Auth::user();

        $hasPending = ReviewerApplication::where('user_id', $user->id)
            ->where('status', ReviewerApplication::STATUS_PENDING)
            ->exists();

        if ($hasPending) {
            return redirect()->route('reviewer.application.status')->with('error', 'You already have a pending application.');
        }

        $journals = Journal::all();

        return view('journal.reviewer.apply', compact('user', 'journals'));
    }

    /**
     * Store a new reviewer application.
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        $hasPending = ReviewerApplication::where('user_id', $user->id)
            ->where('status', ReviewerApplication::STATUS_PENDING)
            ->exists();

        if ($hasPending) {
            return redirect()->route('reviewer.application.status')->with('error', 'You already have a pending application.');
        }

        $validated = $request->validate([
            'journal_id' => 'required|exists:journals,id',
            'affiliation' => 'required|string|max:255',
            'department' => 'required|string|max:255',
            'academic_position' => 'required|string|max:255',
            
            'orcid' => 'nullable|string|max:255',
            'academic_url' => 'nullable|url|max:255',
            
            'primary_research_area' => 'required|string|max:255',
            'research_keywords' => 'nullable|string|max:1000',
            'expertise' => 'nullable|string|max:1000',
            
            'years_of_experience' => 'required|integer|min:0|max:100',
            'previous_experience' => 'nullable|string|max:2000',
            
            'available_for_review' => 'boolean',
            'max_reviews_per_month' => 'required|integer|min:1|max:50',
            
            'agreed_confidentiality' => 'accepted',
            'agreed_conflict_of_interest' => 'accepted',
            'agreed_guidelines' => 'accepted',
        ]);

        $validated['user_id'] = $user->id;
        $validated['status'] = ReviewerApplication::STATUS_PENDING;

        // Force booleans to strict booleans (accepted validation returns strings like "1" or "on")
        $validated['agreed_confidentiality'] = true;
        $validated['agreed_conflict_of_interest'] = true;
        $validated['agreed_guidelines'] = true;
        $validated['available_for_review'] = $request->has('available_for_review');

        ReviewerApplication::create($validated);

        // UAM.3: Sync to Canonical AcademicProfile
        \App\Models\AcademicProfile::updateOrCreate(
            ['user_id' => $user->id],
            [
                'institution' => $validated['affiliation'],
                'department' => $validated['department'],
                'academic_position' => $validated['academic_position'],
                'orcid' => $validated['orcid'] ?? null,
                'google_scholar_url' => $validated['academic_url'] ?? null,
                'research_interests' => $validated['primary_research_area'],
                'expertise' => isset($validated['expertise']) ? explode(',', $validated['expertise']) : null,
            ]
        );

        return redirect()->route('reviewer.application.status')->with('success', 'Your reviewer application has been submitted and is awaiting editorial review.');
    }
}
