<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Journal;
use App\Models\JournalMembership;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class JournalMembershipController extends Controller
{
    /**
     * Display a listing of the journal memberships.
     */
    public function index(Request $request, Journal $journal)
    {
        Gate::authorize('viewAny', [JournalMembership::class, $journal]);

        $memberships = $journal->memberships()->with('user:id,name,email')->get();

        return response()->json(['data' => $memberships]);
    }

    /**
     * Store a newly created membership in storage.
     */
    public function store(Request $request, Journal $journal)
    {
        Gate::authorize('create', [JournalMembership::class, $journal]);

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'role' => ['required', Rule::in(['owner', 'editor', 'reviewer'])],
            'status' => ['required', Rule::in(['active', 'inactive', 'suspended'])],
        ]);

        // Protection against self-escalation (Admin/Root modification not allowed via this endpoint)
        if ($request->has('is_admin') || $request->has('role') && !in_array($request->role, ['owner', 'editor', 'reviewer'])) {
            return response()->json(['message' => 'Invalid role or privilege escalation attempt detected.'], 403);
        }

        // Duplicate check
        $exists = $journal->memberships()
            ->where('user_id', $validated['user_id'])
            ->where('role', $validated['role'])
            ->exists();

        if ($exists) {
            return response()->json(['message' => 'User already has this role in the journal.'], 422);
        }

        $membership = $journal->memberships()->create($validated);

        return response()->json(['message' => 'Membership created successfully.', 'data' => $membership], 201);
    }

    /**
     * Update the specified membership in storage.
     */
    public function update(Request $request, Journal $journal, JournalMembership $membership)
    {
        if ($membership->journal_id !== $journal->id) {
            abort(404);
        }

        Gate::authorize('update', $membership);

        $validated = $request->validate([
            'role' => ['sometimes', 'required', Rule::in(['owner', 'editor', 'reviewer'])],
            'status' => ['sometimes', 'required', Rule::in(['active', 'inactive', 'suspended'])],
        ]);

        // Protection against self-escalation
        if ($request->has('is_admin') || $request->has('role') && !in_array($request->role, ['owner', 'editor', 'reviewer'])) {
            return response()->json(['message' => 'Invalid role or privilege escalation attempt detected.'], 403);
        }

        // Duplicate check if role is changed
        if (isset($validated['role']) && $validated['role'] !== $membership->role) {
            $exists = $journal->memberships()
                ->where('user_id', $membership->user_id)
                ->where('role', $validated['role'])
                ->where('id', '!=', $membership->id)
                ->exists();

            if ($exists) {
                return response()->json(['message' => 'User already has this role in the journal.'], 422);
            }
        }

        $membership->update($validated);

        return response()->json(['message' => 'Membership updated successfully.', 'data' => $membership]);
    }

    /**
     * Remove the specified membership from storage.
     */
    public function destroy(Journal $journal, JournalMembership $membership)
    {
        if ($membership->journal_id !== $journal->id) {
            abort(404);
        }

        Gate::authorize('delete', $membership);

        $membership->delete();

        return response()->json(['message' => 'Membership removed successfully.']);
    }
}
