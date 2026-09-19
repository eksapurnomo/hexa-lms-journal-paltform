<?php

namespace Tests\Feature;

use App\Models\Journal;
use App\Models\JournalMembership;
use App\Models\JournalMembershipApplication;
use App\Models\Submission;
use App\Models\SubmissionRevision;
use App\Models\ReviewRound;
use App\Models\ReviewAssignment;
use App\Models\PeerReview;
use App\Models\EditorialDecision;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JournalProcessFlowTest extends TestCase
{
    use RefreshDatabase;

    private function makeJournal($title = 'Test Journal'): Journal
    {
        return Journal::create([
            'title'  => $title,
            'slug'   => \Illuminate\Support\Str::uuid(),
            'status' => 'active',
        ]);
    }

    private function makeMember(Journal $journal, string $role, string $status = 'active'): User
    {
        $user = User::factory()->create();
        JournalMembership::create([
            'journal_id' => $journal->id,
            'user_id'    => $user->id,
            'role'       => $role,
            'status'     => $status,
        ]);
        return $user;
    }

    private function makeAdmin(): User
    {
        return User::factory()->create(['is_admin' => true]);
    }

    public function test_admin_can_access_process_flow()
    {
        $admin = $this->makeAdmin();
        $journal = $this->makeJournal();

        $response = $this->actingAs($admin)->get(route('admin.journal.process-flow'));
        $response->assertStatus(200);
        $response->assertSee($journal->title);
    }

    public function test_unauthorized_user_cannot_access()
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)->get(route('admin.journal.process-flow'));
        $response->assertStatus(403); // Assuming standard admin middleware redirects or aborts
    }

    public function test_journal_owner_sees_only_authorized_journal()
    {
        $journalA = $this->makeJournal('Journal A');
        $journalB = $this->makeJournal('Journal B');

        $ownerA = $this->makeMember($journalA, 'owner');

        $response = $this->actingAs($ownerA)->get(route('admin.journal.process-flow'));
        $response->assertStatus(200);
        $response->assertSee('Journal A');
        $response->assertDontSee('Journal B');
    }

    public function test_editor_cannot_see_another_journal()
    {
        $journalA = $this->makeJournal('Journal A');
        $journalB = $this->makeJournal('Journal B');

        $editorA = $this->makeMember($journalA, 'editor');

        $response = $this->actingAs($editorA)->get(route('admin.journal.process-flow'));
        $response->assertStatus(200);
        $response->assertSee('Journal A');
        $response->assertDontSee('Journal B');
    }

    public function test_journal_selector_cannot_be_used_for_idor()
    {
        $journalA = $this->makeJournal('Journal A');
        $journalB = $this->makeJournal('Journal B');

        $ownerA = $this->makeMember($journalA, 'owner');

        // Try to access Journal B via selector
        $response = $this->actingAs($ownerA)->get(route('admin.journal.process-flow', ['journal_id' => $journalB->id]));
        $response->assertStatus(403);
    }

    public function test_membership_counts_are_correct()
    {
        $journal = $this->makeJournal();
        $admin = $this->makeAdmin();
        $this->makeMember($journal, 'reviewer', 'active');
        $this->makeMember($journal, 'reviewer', 'pending');
        $this->makeMember($journal, 'editor', 'suspended');

        $response = $this->actingAs($admin)->get(route('admin.journal.process-flow', ['journal_id' => $journal->id]));
        
        $response->assertSeeInOrder(['① MEMBERSHIP', 'Active', '1', 'Pending', '1', 'Suspended/Revoked', '1']);
    }

    public function test_verification_counts_are_correct()
    {
        $journal = $this->makeJournal();
        $admin = $this->makeAdmin();
        $user = User::factory()->create();

        JournalMembershipApplication::create([
            'journal_id' => $journal->id,
            'user_id' => $user->id,
            'requested_role' => 'reviewer',
            'status' => 'under_review'
        ]);

        $response = $this->actingAs($admin)->get(route('admin.journal.process-flow', ['journal_id' => $journal->id]));
        $response->assertSeeInOrder(['② VERIFICATION', 'Approved', '0', 'Under Review', '1']);
    }

    public function test_editorial_counts_are_correct()
    {
        $journal = $this->makeJournal();
        $admin = $this->makeAdmin();
        $user = User::factory()->create();

        // Unassigned
        Submission::forceCreate([
            'created_by' => $user->id,
            'journal_id' => $journal->id,
            'title'      => 'S1',
            'status'     => 'submitted',
        ]);
        
        // Assigned
        Submission::forceCreate([
            'created_by' => $user->id,
            'journal_id' => $journal->id,
            'title'      => 'S2',
            'status'     => 'submitted',
            'editor_id'  => $user->id,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.journal.process-flow', ['journal_id' => $journal->id]));
        $response->assertSeeInOrder(['③ EDITORIAL', 'Assigned', '1', 'Unassigned', '1']);
    }

    public function test_reviewer_selection_and_peer_review_counts_are_correct()
    {
        $journal = $this->makeJournal();
        $admin = $this->makeAdmin();
        $user = User::factory()->create();

        $sub = Submission::forceCreate([
            'created_by' => $user->id,
            'journal_id' => $journal->id,
            'title'      => 'S1',
            'status'     => 'in_review',
        ]);

        $rev = SubmissionRevision::create(['submission_id' => $sub->id, 'version_number' => 1]);
        
        $round = ReviewRound::create([
            'submission_revision_id' => $rev->id, 
            'round_number' => 1,
            'minimum_reviewers' => 1,
            'target_reviewers' => 2,
            'maximum_reviewers' => 3,
            'review_model' => 'single_blind',
        ]);

        // Awaiting ReviewAssignment
        $assignment = ReviewAssignment::create([
            'review_round_id' => $round->id,
            'reviewer_id'     => $user->id,
            'assigned_by'     => $admin->id,
            'status'          => 'assigned',
            'review_mode'     => 'single_blind',
            'assigned_at'     => now(),
        ]);

        $response = $this->actingAs($admin)->get(route('admin.journal.process-flow', ['journal_id' => $journal->id]));
        $response->assertSeeInOrder(['④ REVIEWER SELECTION', 'Rounds', '1', 'Awaiting', '1', 'Assigned', '0']);
        
        // Peer Review should be 0 completed, 0 pending since nothing is accepted yet
        $response->assertSeeInOrder(['⑤ PEER REVIEW', 'Completed', '0', 'Pending', '0']);

        // Accept the assignment
        $assignment->update(['status' => 'accepted']);
        $response2 = $this->actingAs($admin)->get(route('admin.journal.process-flow', ['journal_id' => $journal->id]));
        // Peer review pending = 1
        $response2->assertSeeInOrder(['⑤ PEER REVIEW', 'Completed', '0', 'Pending', '1']);

        // Submit the peer review
        $assignment->update(['status' => 'submitted']);
        PeerReview::create([
            'review_assignment_id' => $assignment->id,
            'recommendation' => 'accept',
            'comments_to_editor' => 'good',
        ]);

        $response3 = $this->actingAs($admin)->get(route('admin.journal.process-flow', ['journal_id' => $journal->id]));
        $response3->assertSeeInOrder(['⑤ PEER REVIEW', 'Completed', '1', 'Pending', '0']);
        
        // Check Recommendation
        $response3->assertSeeInOrder(['⑥ RECOMMENDATION', 'Submitted', '1', 'Pending', '0']);
    }

    public function test_editorial_decision_counts_are_correct()
    {
        $journal = $this->makeJournal();
        $admin = $this->makeAdmin();
        $user = User::factory()->create();

        $sub = Submission::forceCreate([
            'created_by' => $user->id,
            'journal_id' => $journal->id,
            'title'      => 'S1',
            'status'     => 'in_review',
        ]);
        $rev = SubmissionRevision::create(['submission_id' => $sub->id, 'version_number' => 1]);
        $round = ReviewRound::create([
            'submission_revision_id' => $rev->id, 
            'round_number' => 1,
            'minimum_reviewers' => 1,
            'target_reviewers' => 2,
            'maximum_reviewers' => 3,
            'review_model' => 'single_blind',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.journal.process-flow', ['journal_id' => $journal->id]));
        $response->assertSeeInOrder(['⑦ EDITORIAL DECISION', 'Completed', '0', 'Pending', '1']);

        EditorialDecision::create([
            'review_round_id' => $round->id,
            'user_id' => $admin->id,
            'decision' => 'accept',
        ]);

        $response2 = $this->actingAs($admin)->get(route('admin.journal.process-flow', ['journal_id' => $journal->id]));
        $response2->assertSeeInOrder(['⑦ EDITORIAL DECISION', 'Completed', '1', 'Pending', '0']);
    }
}
