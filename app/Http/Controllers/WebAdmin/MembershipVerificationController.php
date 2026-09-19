<?php

namespace App\Http\Controllers\WebAdmin;

use App\Http\Controllers\Controller;
use App\Models\JournalMembership;
use App\Models\JournalMembershipApplication;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class MembershipVerificationController extends Controller
{
    public function index(Request $request)
    {
        $query = JournalMembershipApplication::with(['user.academicProfile', 'journal'])
            ->orderBy('created_at', 'desc');

        if ($request->has('status') && $request->status !== '') {
            $query->where('status', $request->status);
        }
        
        if ($request->has('journal_id') && $request->journal_id !== '') {
            $query->where('journal_id', $request->journal_id);
        }

        $applications = $query->paginate(20);
        $journals = \App\Models\Journal::where('status', 'active')->get();

        return view('admin.membership-verification.index', compact('applications', 'journals'));
    }

    public function show($id)
    {
        $application = JournalMembershipApplication::with([
            'user.academicProfile.institutionRelation',
            'journal',
            'verificationEvidences'
        ])->findOrFail($id);

        return view('admin.membership-verification.show', compact('application'));
    }

    public function updateStatus(Request $request, $id)
    {
        $application = JournalMembershipApplication::findOrFail($id);
        $admin = Auth::user();

        $validated = $request->validate([
            'status' => ['required', Rule::in([
                JournalMembershipApplication::STATUS_UNDER_REVIEW,
                JournalMembershipApplication::STATUS_NEEDS_REVISION,
                JournalMembershipApplication::STATUS_APPROVED,
                JournalMembershipApplication::STATUS_REJECTED,
            ])],
            'reviewer_note' => 'nullable|string|max:1000',
        ]);

        try {
            DB::beginTransaction();

            $application->status = $validated['status'];
            $application->reviewer_note = $validated['reviewer_note'] ?? null;
            
            // Only update reviewed_at and reviewed_by for decisions
            if (in_array($validated['status'], [
                JournalMembershipApplication::STATUS_APPROVED,
                JournalMembershipApplication::STATUS_REJECTED,
                JournalMembershipApplication::STATUS_NEEDS_REVISION
            ])) {
                $application->reviewed_by = $admin->id;
                $application->reviewed_at = now();
            }

            if ($validated['status'] === JournalMembershipApplication::STATUS_APPROVED) {
                // Check if active membership exists
                $activeExists = JournalMembership::where('user_id', $application->user_id)
                    ->where('journal_id', $application->journal_id)
                    ->where('role', $application->requested_role)
                    ->where('status', 'active')
                    ->exists();

                if ($activeExists) {
                    throw new \Exception("An active {$application->requested_role} membership already exists for this user in this journal.");
                }

                // Check for pending membership
                $pendingMembership = JournalMembership::where('user_id', $application->user_id)
                    ->where('journal_id', $application->journal_id)
                    ->where('role', $application->requested_role)
                    ->where('status', 'pending')
                    ->first();

                if ($pendingMembership) {
                    $pendingMembership->update(['status' => 'active']);
                    $membership = $pendingMembership;
                } else {
                    $membership = JournalMembership::create([
                        'user_id' => $application->user_id,
                        'journal_id' => $application->journal_id,
                        'role' => $application->requested_role,
                        'status' => 'active',
                    ]);
                }

                if ($application->requested_role === 'reviewer') {
                    \App\Models\JournalReviewerCapability::firstOrCreate(
                        ['journal_membership_id' => $membership->id],
                        [
                            'available_for_review' => true,
                            'max_reviews_per_month' => 2,
                        ]
                    );
                }
            }

            $application->save();
            DB::commit();

            return redirect()->route('admin.membership-verifications.show', $application->id)
                ->with('success', 'Application status updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            \Illuminate\Support\Facades\Log::error('Verification Error: ' . $e->getMessage() . ' at ' . $e->getFile() . ':' . $e->getLine());
            return back()->with('error', 'Error updating application: ' . $e->getMessage());
        }
    }
}
