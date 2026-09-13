<?php

namespace App\Http\Controllers;

use App\Http\Requests\JournalStoreRequest;
use App\Http\Requests\JournalUpdateRequest;
use App\Models\Journal;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class JournalController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', Journal::class);

        $user = Auth::user();
        if ($user->hasRole('admin') || $user->is_admin) {
            $journals = Journal::all();
        } else {
            $journals = $user->journals()->wherePivot('status', 'active')->get();
        }

        return response()->json(['journals' => $journals]);
    }

    public function store(JournalStoreRequest $request)
    {
        $this->authorize('create', Journal::class);

        $user = Auth::user();

        $journal = Journal::create([
            'title' => $request->title,
            'slug' => $request->slug,
            'description' => $request->description,
            'issn' => $request->issn,
            'eissn' => $request->eissn,
            'status' => $request->status ?? 'draft',
            'created_by' => $user->id,
        ]);

        $journal->peerReviewPolicy()->create([
            'review_model' => $request->review_model ?? \App\Models\PeerReviewPolicy::DEFAULT_REVIEW_MODEL,
            'minimum_reviewers' => $request->minimum_reviewers ?? \App\Models\PeerReviewPolicy::DEFAULT_MIN_REVIEWERS,
            'target_reviewers' => $request->target_reviewers ?? \App\Models\PeerReviewPolicy::DEFAULT_TARGET_REVIEWERS,
            'maximum_reviewers' => $request->maximum_reviewers ?? \App\Models\PeerReviewPolicy::DEFAULT_MAX_REVIEWERS,
            'reviewer_agreement_required' => $request->reviewer_agreement_required ?? false,
            'conflict_of_interest_required' => $request->conflict_of_interest_required ?? false,
            'confidentiality_required' => $request->confidentiality_required ?? false,
        ]);

        return response()->json(['journal' => $journal->load('peerReviewPolicy')], 201);
    }

    public function update(JournalUpdateRequest $request, Journal $journal)
    {
        $this->authorize('update', $journal);

        $journal->update([
            'title' => $request->title,
            'slug' => $request->slug,
            'description' => $request->description,
            'issn' => $request->issn,
            'eissn' => $request->eissn,
            'status' => $request->status ?? $journal->status,
        ]);

        if ($request->hasAny(['review_model', 'minimum_reviewers', 'target_reviewers', 'maximum_reviewers', 'reviewer_agreement_required', 'conflict_of_interest_required', 'confidentiality_required'])) {
            $policy = $journal->peerReviewPolicy ?: new \App\Models\PeerReviewPolicy();
            $policy->review_model = $request->review_model ?? $policy->review_model ?? \App\Models\PeerReviewPolicy::DEFAULT_REVIEW_MODEL;
            $policy->minimum_reviewers = $request->minimum_reviewers ?? $policy->minimum_reviewers ?? \App\Models\PeerReviewPolicy::DEFAULT_MIN_REVIEWERS;
            $policy->target_reviewers = $request->target_reviewers ?? $policy->target_reviewers ?? \App\Models\PeerReviewPolicy::DEFAULT_TARGET_REVIEWERS;
            $policy->maximum_reviewers = $request->maximum_reviewers ?? $policy->maximum_reviewers ?? \App\Models\PeerReviewPolicy::DEFAULT_MAX_REVIEWERS;
            $policy->reviewer_agreement_required = $request->reviewer_agreement_required ?? $policy->reviewer_agreement_required ?? false;
            $policy->conflict_of_interest_required = $request->conflict_of_interest_required ?? $policy->conflict_of_interest_required ?? false;
            $policy->confidentiality_required = $request->confidentiality_required ?? $policy->confidentiality_required ?? false;
            
            $journal->peerReviewPolicy()->save($policy);
        }

        return response()->json(['journal' => $journal->load('peerReviewPolicy')], 200);
    }

    public function delete(Journal $journal)
    {
        $this->authorize('delete', $journal);

        $journal->delete();

        return response()->json(['message' => 'Deleted successfully'], 200);
    }
}
