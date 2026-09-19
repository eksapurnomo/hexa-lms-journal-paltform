<?php

namespace App\Http\Controllers\WebAdmin;

use App\Http\Controllers\Controller;
use App\Models\Journal;
use App\Services\JournalProcessFlowService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class JournalProcessFlowController extends Controller
{
    protected JournalProcessFlowService $processFlowService;

    public function __construct(JournalProcessFlowService $processFlowService)
    {
        $this->processFlowService = $processFlowService;
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        
        // Fetch accessible journals
        if ($user->is_admin || $user->hasRole('admin')) {
            $journals = Journal::with('primarySubjects.parent.parent')
                ->where('status', 'active')->get();
        } else {
            $journals = Journal::with('primarySubjects.parent.parent')
                ->where('status', 'active')
                ->whereHas('memberships', function ($q) use ($user) {
                    $q->where('user_id', $user->id)
                      ->where('status', 'active')
                      ->whereIn('role', ['owner', 'editor']);
                })->get();
        }

        if ($journals->isEmpty()) {
            abort(403, 'Unauthorized action. No accessible journals.');
        }

        $selectedJournalId = $request->get('journal_id');
        
        if ($selectedJournalId) {
            $selectedJournal = $journals->firstWhere('id', $selectedJournalId);
            if (! $selectedJournal) {
                abort(403, 'Unauthorized action. You do not have access to this journal.');
            }
        } else {
            $selectedJournal = $journals->first();
        }

        $stats = [
            'membership' => $this->processFlowService->getMembershipStats($selectedJournal->id),
            'verification' => $this->processFlowService->getVerificationStats($selectedJournal->id),
            'editorial' => $this->processFlowService->getEditorialStats($selectedJournal->id),
            'reviewerSelection' => $this->processFlowService->getReviewerSelectionStats($selectedJournal->id),
            'peerReview' => $this->processFlowService->getPeerReviewStats($selectedJournal->id),
            'recommendation' => $this->processFlowService->getRecommendationStats($selectedJournal->id),
            'decision' => $this->processFlowService->getEditorialDecisionStats($selectedJournal->id),
        ];

        $currentProcess = $this->processFlowService->getCurrentProcess($selectedJournal->id);

        return view('admin.journal_process_flow.index', compact('journals', 'selectedJournal', 'stats', 'currentProcess'));
    }
}
