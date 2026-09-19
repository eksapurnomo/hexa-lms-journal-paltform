<?php

namespace Tests\Feature;

use App\Models\AcademicProfile;
use App\Models\Journal;
use App\Models\JournalMembership;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PhaseUAMStep1Test extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        // Ensure admin role exists
        if (!Role::where('name', 'admin')->exists()) {
            Role::create(['name' => 'admin']);
        }
    }

    private function createUser()
    {
        return User::factory()->create();
    }

    private function createAdmin()
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $admin->assignRole('admin');
        return $admin;
    }

    private function createJournal()
    {
        return Journal::create([
            'title' => 'Test Journal ' . uniqid(),
            'slug' => 'test-journal-' . uniqid(),
            'status' => 'active'
        ]);
    }

    private function addMember(Journal $journal, User $user, string $role)
    {
        return JournalMembership::create([
            'journal_id' => $journal->id,
            'user_id' => $user->id,
            'role' => $role,
            'status' => 'active',
        ]);
    }

    /**
     * Test 1 — Academic Profile
     */
    public function test_academic_profile_relation()
    {
        $user = $this->createUser();
        $profile = AcademicProfile::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(AcademicProfile::class, $user->academicProfile);
        $this->assertEquals($profile->id, $user->academicProfile->id);
    }

    /**
     * Test 2 — Admin manages memberships
     */
    public function test_admin_can_manage_memberships()
    {
        $admin = $this->createAdmin();
        $journal = $this->createJournal();
        $user = $this->createUser();

        $response = $this->actingAs($admin, 'api')
            ->postJson("/api/admin/journals/{$journal->id}/memberships", [
                'user_id' => $user->id,
                'role' => 'editor',
                'status' => 'active',
            ]);

        $response->assertCreated();
        $this->assertDatabaseHas('journal_memberships', [
            'user_id' => $user->id,
            'journal_id' => $journal->id,
            'role' => 'editor',
        ]);

        $membership = JournalMembership::where('user_id', $user->id)->first();
        $response = $this->actingAs($admin, 'api')
            ->putJson("/api/admin/journals/{$journal->id}/memberships/{$membership->id}", [
                'status' => 'inactive',
            ]);
        $response->assertOk();

        $response = $this->actingAs($admin, 'api')
            ->deleteJson("/api/admin/journals/{$journal->id}/memberships/{$membership->id}");
        $response->assertOk();
    }

    /**
     * Test 3 — Journal Owner manages own journal
     */
    public function test_owner_can_manage_own_memberships()
    {
        $owner = $this->createUser();
        $journal = $this->createJournal();
        $this->addMember($journal, $owner, 'owner');

        $user = $this->createUser();

        $response = $this->actingAs($owner, 'api')
            ->postJson("/api/admin/journals/{$journal->id}/memberships", [
                'user_id' => $user->id,
                'role' => 'editor',
                'status' => 'active',
            ]);

        $response->assertCreated();
    }

    /**
     * Test 4 — Journal Owner cannot manage another journal
     */
    public function test_owner_cannot_manage_other_journal()
    {
        $owner = $this->createUser();
        $journalA = $this->createJournal();
        $this->addMember($journalA, $owner, 'owner');

        $journalB = $this->createJournal();
        $user = $this->createUser();

        $response = $this->actingAs($owner, 'api')
            ->postJson("/api/admin/journals/{$journalB->id}/memberships", [
                'user_id' => $user->id,
                'role' => 'editor',
                'status' => 'active',
            ]);

        $response->assertForbidden();
    }

    /**
     * Test 5 — Editor cannot manage memberships
     */
    public function test_editor_cannot_manage_memberships()
    {
        $editor = $this->createUser();
        $journal = $this->createJournal();
        $this->addMember($journal, $editor, 'editor');

        $user = $this->createUser();

        $response = $this->actingAs($editor, 'api')
            ->postJson("/api/admin/journals/{$journal->id}/memberships", [
                'user_id' => $user->id,
                'role' => 'reviewer',
                'status' => 'active',
            ]);

        $response->assertForbidden();
    }

    /**
     * Test 6 — Reviewer cannot manage memberships
     */
    public function test_reviewer_cannot_manage_memberships()
    {
        $reviewer = $this->createUser();
        $journal = $this->createJournal();
        $this->addMember($journal, $reviewer, 'reviewer');

        $user = $this->createUser();

        $response = $this->actingAs($reviewer, 'api')
            ->postJson("/api/admin/journals/{$journal->id}/memberships", [
                'user_id' => $user->id,
                'role' => 'editor',
                'status' => 'active',
            ]);

        $response->assertForbidden();
    }

    /**
     * Test 7 — Cross-journal privilege escalation
     */
    public function test_cross_journal_escalation_denied()
    {
        $owner = $this->createUser();
        $journalA = $this->createJournal();
        $this->addMember($journalA, $owner, 'owner');
        
        $journalB = $this->createJournal();
        $user = $this->createUser();
        $membershipB = $this->addMember($journalB, $user, 'editor');

        // Try to update membership in Journal B while acting as owner of Journal A
        $response = $this->actingAs($owner, 'api')
            ->putJson("/api/admin/journals/{$journalB->id}/memberships/{$membershipB->id}", [
                'role' => 'owner'
            ]);

        $response->assertForbidden();
    }

    /**
     * Test 8 & 9 — Platform Admin escalation protection & Self escalation
     */
    public function test_escalation_protection()
    {
        $owner = $this->createUser();
        $journal = $this->createJournal();
        $ownerMembership = $this->addMember($journal, $owner, 'owner');
        
        $user = $this->createUser();

        // Test 8
        $response = $this->actingAs($owner, 'api')
            ->postJson("/api/admin/journals/{$journal->id}/memberships", [
                'user_id' => $user->id,
                'role' => 'owner',
                'status' => 'active',
                'is_admin' => true,
            ]);

        $response->assertForbidden();
        
        // Test 9
        $response = $this->actingAs($owner, 'api')
            ->putJson("/api/admin/journals/{$journal->id}/memberships/{$ownerMembership->id}", [
                'is_admin' => true,
            ]);
            
        $response->assertForbidden();
    }

    /**
     * Test 10 — Valid journal roles only
     */
    public function test_valid_journal_roles_only()
    {
        $owner = $this->createUser();
        $journal = $this->createJournal();
        $this->addMember($journal, $owner, 'owner');
        
        $user = $this->createUser();

        $response = $this->actingAs($owner, 'api')
            ->postJson("/api/admin/journals/{$journal->id}/memberships", [
                'user_id' => $user->id,
                'role' => 'platform_admin',
                'status' => 'active',
            ]);

        $response->assertStatus(422); // Validation fails
    }

    /**
     * Test 11 — Duplicate membership
     */
    public function test_duplicate_membership_denied()
    {
        $owner = $this->createUser();
        $journal = $this->createJournal();
        $this->addMember($journal, $owner, 'owner');
        
        $user = $this->createUser();
        $this->addMember($journal, $user, 'editor');

        $response = $this->actingAs($owner, 'api')
            ->postJson("/api/admin/journals/{$journal->id}/memberships", [
                'user_id' => $user->id,
                'role' => 'editor',
                'status' => 'active',
            ]);

        $response->assertStatus(422);
    }

    /**
     * Test 12 — Existing editorial authorization regression
     */
    public function test_unassigned_editor_cannot_access_submission()
    {
        $owner = $this->createUser();
        $journal = $this->createJournal();
        $this->addMember($journal, $owner, 'owner');
        
        $editorA = $this->createUser();
        $this->addMember($journal, $editorA, 'editor');

        $editorB = $this->createUser();
        $this->addMember($journal, $editorB, 'editor');
        
        // Create a submission assigned to Editor A
        $submission = \App\Models\Submission::forceCreate([
            'journal_id' => $journal->id,
            'editor_id' => $editorA->id,
            'created_by' => $owner->id,
            'title' => 'Test',
            'abstract' => 'Test abstract',
            'status' => 'review_pending'
        ]);

        // Editor B tries to access it
        $response = $this->actingAs($editorB, 'web')->getJson("/api/editorial/submissions/{$submission->id}");
        $response->assertForbidden();
        
        // Editor A can access it
        $response = $this->actingAs($editorA, 'web')->getJson("/api/editorial/submissions/{$submission->id}");
        $response->assertOk();
    }

    /**
     * Test 13 — Existing reviewer authorization regression
     */
    public function test_reviewer_cannot_access_editorial_desk()
    {
        $reviewer = $this->createUser();
        $journal = $this->createJournal();
        $this->addMember($journal, $reviewer, 'reviewer');
        
        $submission = \App\Models\Submission::forceCreate([
            'journal_id' => $journal->id,
            'created_by' => $reviewer->id,
            'title' => 'Test',
            'abstract' => 'Test abstract',
            'status' => 'review_pending'
        ]);

        // Reviewer tries to access editorial desk
        $response = $this->actingAs($reviewer, 'web')->getJson("/api/editorial/submissions/{$submission->id}");
        $response->assertForbidden();
    }
}
