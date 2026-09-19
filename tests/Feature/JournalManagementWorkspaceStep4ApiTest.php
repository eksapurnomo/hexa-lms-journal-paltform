<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Journal;
use App\Models\JournalMembership;
use App\Models\JournalReviewerCapability;
use App\Models\Submission;

class JournalManagementWorkspaceStep4ApiTest extends TestCase
{
    use RefreshDatabase;

    // --- MEMBERS TESTS ---

    public function test_owner_can_access_members()
    {
        $owner = User::factory()->create();
        $journal = Journal::create(['title' => 'Test', 'slug' => 'test', 'status' => 'active', 'created_by' => $owner->id]);
        JournalMembership::create(['user_id' => $owner->id, 'journal_id' => $journal->id, 'role' => 'owner', 'status' => 'active']);

        $response = $this->actingAs($owner, 'api')->getJson("/api/user/journals/{$journal->slug}/management/members");
        
        $response->assertStatus(200);
        $response->assertJsonPath('data.0.role', 'owner');
    }

    public function test_editor_can_access_members()
    {
        $owner = User::factory()->create();
        $editor = User::factory()->create();
        $journal = Journal::create(['title' => 'Test', 'slug' => 'test', 'status' => 'active', 'created_by' => $owner->id]);
        JournalMembership::create(['user_id' => $editor->id, 'journal_id' => $journal->id, 'role' => 'editor', 'status' => 'active']);

        $response = $this->actingAs($editor, 'api')->getJson("/api/user/journals/{$journal->slug}/management/members");
        
        $response->assertStatus(200);
    }

    public function test_reviewer_cannot_access_members()
    {
        $owner = User::factory()->create();
        $reviewer = User::factory()->create();
        $journal = Journal::create(['title' => 'Test', 'slug' => 'test', 'status' => 'active', 'created_by' => $owner->id]);
        JournalMembership::create(['user_id' => $reviewer->id, 'journal_id' => $journal->id, 'role' => 'reviewer', 'status' => 'active']);

        $response = $this->actingAs($reviewer, 'api')->getJson("/api/user/journals/{$journal->slug}/management/members");
        
        $response->assertStatus(403);
    }

    public function test_member_cannot_access_members()
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();
        $journal = Journal::create(['title' => 'Test', 'slug' => 'test', 'status' => 'active', 'created_by' => $owner->id]);
        JournalMembership::create(['user_id' => $member->id, 'journal_id' => $journal->id, 'role' => 'member', 'status' => 'active']);

        $response = $this->actingAs($member, 'api')->getJson("/api/user/journals/{$journal->slug}/management/members");
        
        $response->assertStatus(403);
    }

    public function test_unrelated_user_cannot_access_members()
    {
        $owner = User::factory()->create();
        $unrelated = User::factory()->create();
        $journal = Journal::create(['title' => 'Test', 'slug' => 'test', 'status' => 'active', 'created_by' => $owner->id]);

        $response = $this->actingAs($unrelated, 'api')->getJson("/api/user/journals/{$journal->slug}/management/members");
        
        $response->assertStatus(403);
    }

    public function test_cross_journal_access_denied()
    {
        $ownerA = User::factory()->create();
        $journalA = Journal::create(['title' => 'Test A', 'slug' => 'test-a', 'status' => 'active', 'created_by' => $ownerA->id]);
        JournalMembership::create(['user_id' => $ownerA->id, 'journal_id' => $journalA->id, 'role' => 'owner', 'status' => 'active']);

        $ownerB = User::factory()->create();
        $journalB = Journal::create(['title' => 'Test B', 'slug' => 'test-b', 'status' => 'active', 'created_by' => $ownerB->id]);
        JournalMembership::create(['user_id' => $ownerB->id, 'journal_id' => $journalB->id, 'role' => 'owner', 'status' => 'active']);

        $response = $this->actingAs($ownerA, 'api')->getJson("/api/user/journals/{$journalB->slug}/management/members");
        $response->assertStatus(403);
    }

    public function test_reviewer_capability_information_is_returned()
    {
        $owner = User::factory()->create();
        $reviewer = User::factory()->create();
        $journal = Journal::create(['title' => 'Test', 'slug' => 'test', 'status' => 'active', 'created_by' => $owner->id]);
        
        JournalMembership::create(['user_id' => $owner->id, 'journal_id' => $journal->id, 'role' => 'owner', 'status' => 'active']);
        $membership = JournalMembership::create(['user_id' => $reviewer->id, 'journal_id' => $journal->id, 'role' => 'reviewer', 'status' => 'active']);
        
        JournalReviewerCapability::create([
            'journal_membership_id' => $membership->id,
            'available_for_review' => true,
            'max_reviews_per_month' => 5,
            'years_of_experience' => 10,
        ]);

        $response = $this->actingAs($owner, 'api')->getJson("/api/user/journals/{$journal->slug}/management/members");
        
        $response->assertStatus(200);
        
        $data = $response->json('data');
        $reviewerData = collect($data)->firstWhere('role', 'reviewer');
        
        $this->assertNotNull($reviewerData['reviewer_capability']);
        $this->assertTrue($reviewerData['reviewer_capability']['available_for_review']);
        $this->assertEquals(5, $reviewerData['reviewer_capability']['max_reviews_per_month']);
        $this->assertEquals(10, $reviewerData['reviewer_capability']['years_of_experience']);
    }

    // --- SETTINGS TESTS ---

    public function test_authorized_user_can_read_settings()
    {
        $owner = User::factory()->create();
        $journal = Journal::create(['title' => 'Test', 'slug' => 'test', 'status' => 'active', 'created_by' => $owner->id]);
        JournalMembership::create(['user_id' => $owner->id, 'journal_id' => $journal->id, 'role' => 'owner', 'status' => 'active']);

        $response = $this->actingAs($owner, 'api')->getJson("/api/user/journals/{$journal->slug}/management/settings");
        
        $response->assertStatus(200);
        $response->assertJsonPath('data.title', 'Test');
    }

    public function test_unauthorized_user_cannot_read_settings()
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();
        $journal = Journal::create(['title' => 'Test', 'slug' => 'test', 'status' => 'active', 'created_by' => $owner->id]);
        JournalMembership::create(['user_id' => $member->id, 'journal_id' => $journal->id, 'role' => 'member', 'status' => 'active']);

        $response = $this->actingAs($member, 'api')->getJson("/api/user/journals/{$journal->slug}/management/settings");
        
        $response->assertStatus(403);
    }

    public function test_authorized_role_can_update_permitted_fields()
    {
        $owner = User::factory()->create();
        $journal = Journal::create(['title' => 'Test', 'slug' => 'test', 'status' => 'active', 'created_by' => $owner->id]);
        JournalMembership::create(['user_id' => $owner->id, 'journal_id' => $journal->id, 'role' => 'owner', 'status' => 'active']);

        $response = $this->actingAs($owner, 'api')->patchJson("/api/user/journals/{$journal->slug}/management/settings", [
            'title' => 'New Title',
            'description' => 'New Desc',
            'issn' => '1234-5678',
            'eissn' => '8765-4321',
        ]);
        
        $response->assertStatus(200);
        $this->assertEquals('New Title', $journal->fresh()->title);
        $this->assertEquals('New Desc', $journal->fresh()->description);
        $this->assertEquals('1234-5678', $journal->fresh()->issn);
        $this->assertEquals('8765-4321', $journal->fresh()->eissn);
    }

    public function test_unauthorized_role_cannot_update_settings()
    {
        $owner = User::factory()->create();
        $reviewer = User::factory()->create();
        $journal = Journal::create(['title' => 'Test', 'slug' => 'test', 'status' => 'active', 'created_by' => $owner->id]);
        JournalMembership::create(['user_id' => $reviewer->id, 'journal_id' => $journal->id, 'role' => 'reviewer', 'status' => 'active']);

        $response = $this->actingAs($reviewer, 'api')->patchJson("/api/user/journals/{$journal->slug}/management/settings", [
            'title' => 'Hacked Title',
        ]);
        
        $response->assertStatus(403);
        $this->assertEquals('Test', $journal->fresh()->title);
    }

    public function test_cross_journal_update_denied()
    {
        $ownerA = User::factory()->create();
        $journalA = Journal::create(['title' => 'Test A', 'slug' => 'test-a', 'status' => 'active', 'created_by' => $ownerA->id]);
        JournalMembership::create(['user_id' => $ownerA->id, 'journal_id' => $journalA->id, 'role' => 'owner', 'status' => 'active']);

        $ownerB = User::factory()->create();
        $journalB = Journal::create(['title' => 'Test B', 'slug' => 'test-b', 'status' => 'active', 'created_by' => $ownerB->id]);
        JournalMembership::create(['user_id' => $ownerB->id, 'journal_id' => $journalB->id, 'role' => 'owner', 'status' => 'active']);

        $response = $this->actingAs($ownerA, 'api')->patchJson("/api/user/journals/{$journalB->slug}/management/settings", [
            'title' => 'Hacked B',
        ]);
        
        $response->assertStatus(403);
        $this->assertEquals('Test B', $journalB->fresh()->title);
    }

    public function test_injection_cannot_bypass_authorization()
    {
        $owner = User::factory()->create();
        $journal = Journal::create(['title' => 'Test', 'slug' => 'test', 'status' => 'active', 'created_by' => $owner->id]);
        JournalMembership::create(['user_id' => $owner->id, 'journal_id' => $journal->id, 'role' => 'owner', 'status' => 'active']);

        $otherUser = User::factory()->create();
        
        $response = $this->actingAs($owner, 'api')->patchJson("/api/user/journals/{$journal->slug}/management/settings", [
            'title' => 'New Title',
            'user_id' => $otherUser->id,
            'role' => 'reviewer',
            'journal_id' => 999,
            'created_by' => $otherUser->id,
            'status' => 'archived'
        ]);
        
        $response->assertStatus(200);
        $journal->refresh();
        $this->assertEquals('New Title', $journal->title);
        $this->assertEquals('active', $journal->status);
        $this->assertEquals($owner->id, $journal->created_by);
    }
    public function test_editor_cannot_read_or_update_settings()
    {
        $owner = User::factory()->create();
        $editor = User::factory()->create();
        $journal = Journal::create(['title' => 'Test', 'slug' => 'test', 'status' => 'active', 'created_by' => $owner->id]);
        JournalMembership::create(['user_id' => $editor->id, 'journal_id' => $journal->id, 'role' => 'editor', 'status' => 'active']);

        // Read DENY
        $responseRead = $this->actingAs($editor, 'api')->getJson("/api/user/journals/{$journal->slug}/management/settings");
        $responseRead->assertStatus(403);

        // Update DENY (PATCH)
        $responseUpdate = $this->actingAs($editor, 'api')->patchJson("/api/user/journals/{$journal->slug}/management/settings", [
            'title' => 'Hacked Title',
        ]);
        $responseUpdate->assertStatus(403);

        // Update DENY (PUT)
        $responseUpdatePut = $this->actingAs($editor, 'api')->putJson("/api/user/journals/{$journal->slug}/management/settings", [
            'title' => 'Hacked Title 2',
        ]);
        $responseUpdatePut->assertStatus(405); // Or 403, depending on routes, but normally route doesn't accept PUT, only PATCH. Wait, if PUT is allowed it should be 403.
    }

    public function test_admin_can_update_settings()
    {
        $owner = User::factory()->create();
        $admin = User::factory()->create(['is_admin' => true]);
        $journal = Journal::create(['title' => 'Test', 'slug' => 'test', 'status' => 'active', 'created_by' => $owner->id]);

        $response = $this->actingAs($admin, 'api')->patchJson("/api/user/journals/{$journal->slug}/management/settings", [
            'title' => 'Admin Title',
        ]);
        
        $response->assertStatus(200);
        $this->assertEquals('Admin Title', $journal->fresh()->title);
    }
}
