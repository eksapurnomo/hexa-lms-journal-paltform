<?php

namespace Tests\Feature;

use App\Models\Institution;
use App\Models\Journal;
use App\Models\JournalMembership;
use App\Models\JournalMembershipApplication;
use App\Models\JournalReviewerCapability;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Jraf3Step2ApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
        $this->otherUser = User::factory()->create();
        
        $this->journal = Journal::create([
            'title' => 'Test Journal',
            'slug' => 'test-journal',
            'status' => 'active',
            'user_id' => $this->user->id,
            'description' => 'Test description',
        ]);
        
        $this->otherJournal = Journal::create([
            'title' => 'Other Journal',
            'slug' => 'other-journal',
            'status' => 'active',
            'user_id' => $this->user->id,
            'description' => 'Other description',
        ]);

        $this->admin = User::factory()->create(['is_admin' => 1]);
    }

    public function test_unauthenticated_user_cannot_access_application_api()
    {
        $response = $this->getJson('/api/journals/' . $this->journal->slug . '/membership-application');
        $response->assertStatus(401);
    }

    public function test_user_can_create_draft()
    {
        $data = [
            'requested_role' => 'reviewer',
            'academic_type' => 'researcher',
        ];

        $response = $this->actingAs($this->user, 'api')->postJson('/api/journals/' . $this->journal->slug . '/membership-application', $data);

        $response->assertStatus(201)
            ->assertJsonPath('data.application.status', JournalMembershipApplication::STATUS_DRAFT)
            ->assertJsonPath('data.application.requested_role', 'reviewer');
    }

    public function test_user_can_retrieve_own_draft()
    {
        JournalMembershipApplication::create([
            'user_id' => $this->user->id,
            'journal_id' => $this->journal->id,
            'requested_role' => 'reviewer',
            'status' => JournalMembershipApplication::STATUS_DRAFT,
        ]);

        $response = $this->actingAs($this->user, 'api')->getJson('/api/journals/' . $this->journal->slug . '/membership-application');

        $response->assertStatus(200)
            ->assertJsonPath('data.application.status', JournalMembershipApplication::STATUS_DRAFT);
    }

    public function test_user_can_update_own_draft()
    {
        JournalMembershipApplication::create([
            'user_id' => $this->user->id,
            'journal_id' => $this->journal->id,
            'requested_role' => 'reviewer',
            'status' => JournalMembershipApplication::STATUS_DRAFT,
        ]);

        $data = [
            'academic_type' => 'lecturer',
            'highest_degree' => 'PhD',
            'recruitment_source' => 'friend',
        ];

        $response = $this->actingAs($this->user, 'api')->patchJson('/api/journals/' . $this->journal->slug . '/membership-application', $data);

        $response->assertStatus(200)
            ->assertJsonPath('data.application.recruitment_source', 'friend');
    }

    public function test_user_can_submit_own_application()
    {
        JournalMembershipApplication::create([
            'user_id' => $this->user->id,
            'journal_id' => $this->journal->id,
            'requested_role' => 'reviewer',
            'status' => JournalMembershipApplication::STATUS_DRAFT,
        ]);

        $response = $this->actingAs($this->user, 'api')->postJson('/api/journals/' . $this->journal->slug . '/membership-application/submit');

        $response->assertStatus(200)
            ->assertJsonPath('data.application.status', JournalMembershipApplication::STATUS_SUBMITTED);
    }

    public function test_user_cannot_retrieve_another_users_application()
    {
        JournalMembershipApplication::create([
            'user_id' => $this->otherUser->id,
            'journal_id' => $this->journal->id,
            'requested_role' => 'reviewer',
            'status' => JournalMembershipApplication::STATUS_DRAFT,
        ]);

        $response = $this->actingAs($this->user, 'api')->getJson('/api/journals/' . $this->journal->slug . '/membership-application');

        $response->assertStatus(404);
    }

    public function test_user_cannot_access_application_belonging_to_another_journal()
    {
        JournalMembershipApplication::create([
            'user_id' => $this->user->id,
            'journal_id' => $this->journal->id,
            'requested_role' => 'reviewer',
            'status' => JournalMembershipApplication::STATUS_DRAFT,
        ]);

        $response = $this->actingAs($this->user, 'api')->getJson('/api/journals/' . $this->otherJournal->slug . '/membership-application');

        $response->assertStatus(404);
    }

    public function test_user_can_retrieve_own_application_status()
    {
        $app = JournalMembershipApplication::create([
            'user_id' => $this->user->id,
            'journal_id' => $this->journal->id,
            'requested_role' => 'reviewer',
            'status' => JournalMembershipApplication::STATUS_REJECTED,
            'reviewer_note' => 'Not enough experience',
        ]);

        $response = $this->actingAs($this->user, 'api')->getJson('/api/journals/' . $this->journal->slug . '/membership-application/status');

        $response->assertStatus(200)
            ->assertJsonPath('data.status.status', JournalMembershipApplication::STATUS_REJECTED)
            ->assertJsonPath('data.status.reviewer_note', 'Not enough experience');
    }

    public function test_user_can_retrieve_own_membership_status()
    {
        JournalMembership::create([
            'user_id' => $this->user->id,
            'journal_id' => $this->journal->id,
            'role' => 'reviewer',
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->user, 'api')->getJson('/api/journals/' . $this->journal->slug . '/membership');

        $response->assertStatus(200)
            ->assertJsonPath('data.membership.role', 'reviewer')
            ->assertJsonPath('data.membership.status', 'active');
    }

    public function test_user_cannot_retrieve_another_users_membership()
    {
        JournalMembership::create([
            'user_id' => $this->otherUser->id,
            'journal_id' => $this->journal->id,
            'role' => 'reviewer',
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->user, 'api')->getJson('/api/journals/' . $this->journal->slug . '/membership');

        $response->assertStatus(404);
    }

    public function test_active_reviewer_can_retrieve_own_capability()
    {
        $membership = JournalMembership::create([
            'user_id' => $this->user->id,
            'journal_id' => $this->journal->id,
            'role' => 'reviewer',
            'status' => 'active',
        ]);

        JournalReviewerCapability::create([
            'journal_membership_id' => $membership->id,
            'available_for_review' => true,
            'max_reviews_per_month' => 2,
        ]);

        $response = $this->actingAs($this->user, 'api')->getJson('/api/journals/' . $this->journal->slug . '/reviewer-capability');

        $response->assertStatus(200)
            ->assertJsonPath('data.capability.available_for_review', true)
            ->assertJsonPath('data.capability.max_reviews_per_month', 2);
    }

    public function test_non_reviewer_cannot_retrieve_reviewer_capability()
    {
        $membership = JournalMembership::create([
            'user_id' => $this->user->id,
            'journal_id' => $this->journal->id,
            'role' => 'member',
            'status' => 'active',
        ]);

        JournalReviewerCapability::create([
            'journal_membership_id' => $membership->id,
            'available_for_review' => true,
            'max_reviews_per_month' => 2,
        ]);

        $response = $this->actingAs($this->user, 'api')->getJson('/api/journals/' . $this->journal->slug . '/reviewer-capability');

        $response->assertStatus(404);
    }

    public function test_inactive_reviewer_cannot_use_reviewer_capability_endpoint()
    {
        $membership = JournalMembership::create([
            'user_id' => $this->user->id,
            'journal_id' => $this->journal->id,
            'role' => 'reviewer',
            'status' => 'pending',
        ]);

        JournalReviewerCapability::create([
            'journal_membership_id' => $membership->id,
            'available_for_review' => true,
            'max_reviews_per_month' => 2,
        ]);

        $response = $this->actingAs($this->user, 'api')->getJson('/api/journals/' . $this->journal->slug . '/reviewer-capability');

        $response->assertStatus(404);
    }
}
