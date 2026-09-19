<?php

namespace App\Http\Controllers\WebAdmin;

use App\Http\Controllers\Controller;
use App\Models\Journal;
use App\Models\User;
use App\Services\JournalUserProcessMonitorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class JournalUserProcessMonitorController extends Controller
{
    protected JournalUserProcessMonitorService $monitorService;

    public function __construct(JournalUserProcessMonitorService $monitorService)
    {
        $this->monitorService = $monitorService;
    }

    private function getAccessibleJournals(User $user)
    {
        if ($user->is_admin || $user->hasRole('admin')) {
            return Journal::with('primarySubjects.parent.parent')
                ->where('status', 'active')->get();
        }

        return Journal::with('primarySubjects.parent.parent')
            ->where('status', 'active')
            ->whereHas('memberships', function ($q) use ($user) {
                $q->where('user_id', $user->id)
                  ->where('status', 'active')
                  ->whereIn('role', ['owner', 'editor']);
            })->get();
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        $journals = $this->getAccessibleJournals($user);

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

        $users = collect();
        if ($selectedJournal) {
            $rawUsers = $this->monitorService->getJournalUsers($selectedJournal->id);
            
            foreach ($rawUsers as $u) {
                $roles = $this->monitorService->getUserRoles($selectedJournal->id, $u->id);
                
                // If filtering by role
                $filterRole = $request->get('role');
                if ($filterRole && !in_array($filterRole, $roles)) {
                    continue;
                }

                // Search by name, email, institution
                $search = $request->get('search');
                if ($search) {
                    $searchLower = strtolower($search);
                    $matches = false;
                    if (str_contains(strtolower($u->name), $searchLower) || str_contains(strtolower($u->email), $searchLower)) {
                        $matches = true;
                    }
                    if ($u->academicProfile && str_contains(strtolower($u->academicProfile->institution), $searchLower)) {
                        $matches = true;
                    }
                    if (!$matches) {
                        continue;
                    }
                }

                // Compute overall primary process summary for table list
                $primaryProcessName = 'Unknown';
                $primaryBlocker = 'No blocker detected.';
                $primaryProgress = '0 / 0';
                
                foreach ($roles as $role) {
                    $proc = $this->monitorService->getUserProcess($selectedJournal->id, $u->id, $role);
                    if ($proc['current_process'] !== 'Unknown') {
                        $primaryProcessName = $proc['current_process'];
                        $primaryBlocker = $proc['current_blocker'];
                        $primaryProgress = $proc['progress_text'];
                        break; // use the first valid role flow
                    }
                }

                $users->push((object)[
                    'id' => $u->id,
                    'name' => $u->name,
                    'email' => $u->email,
                    'institution' => $u->academicProfile->institution ?? 'N/A',
                    'roles' => $roles,
                    'current_process' => $primaryProcessName,
                    'current_blocker' => $primaryBlocker,
                    'progress_text' => $primaryProgress,
                ]);
            }
        }

        return view('admin.journal_user_process_monitor.index', compact('journals', 'selectedJournal', 'users'));
    }

    public function show(Request $request, $journalId, $userId)
    {
        $user = Auth::user();
        $journals = $this->getAccessibleJournals($user);
        $selectedJournal = $journals->firstWhere('id', $journalId);

        if (!$selectedJournal) {
            abort(403, 'Unauthorized action. You do not have access to this journal.');
        }

        $monitoredUser = User::with('academicProfile')->findOrFail($userId);
        
        // Ensure the monitored user is actually connected to the journal
        $roles = $this->monitorService->getUserRoles($selectedJournal->id, $monitoredUser->id);
        
        if (empty($roles)) {
            abort(404, 'User is not associated with this journal.');
        }

        $processes = [];
        foreach ($roles as $role) {
            $processes[$role] = $this->monitorService->getUserProcess($selectedJournal->id, $monitoredUser->id, $role);
        }

        return view('admin.journal_user_process_monitor.show', compact('selectedJournal', 'monitoredUser', 'roles', 'processes'));
    }
}
