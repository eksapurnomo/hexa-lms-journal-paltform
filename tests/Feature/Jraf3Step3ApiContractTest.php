<?php

namespace Tests\Feature;

use App\Models\Journal;
use App\Models\JournalMembership;
use App\Models\JournalMembershipApplication;
use App\Models\JournalReviewerCapability;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Jraf3Step3ApiContractTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
        $this->journal = Journal::create([
            'title' => 'Contract Journal',
            'slug' => 'contract-journal',
            'status' => 'active',
            'user_id' => User::factory()->create()->id,
            'description' => 'Test description',
        ]);
    }

    public function test_api_response_structure_follows_message_data_contract()
    {
        JournalMembershipApplication::create([
            'user_id' => $this->user->id,
            'journal_id' => $this->journal->id,
            'requested_role' => 'reviewer',
            'status' => JournalMembershipApplication::STATUS_DRAFT,
            'reviewed_by' => User::factory()->create()->id, // Should be hidden
        ]);

        $response = $this->actingAs($this->user, 'api')->getJson('/api/journals/' . $this->journal->slug . '/membership-application');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'application' => [
                        'id', 'user_id', 'journal_id', 'requested_role', 'status'
                    ]
                ]
            ])
            ->assertJsonMissing(['reviewed_by' => 999])
            ->assertJsonMissing(['reviewer_note']);
    }

    public function test_api_validation_response_contract()
    {
        $data = [
            'requested_role' => 'invalid_role', // Fails validation
        ];

        $response = $this->actingAs($this->user, 'api')->postJson('/api/journals/' . $this->journal->slug . '/membership-application', $data);

        // Standard Laravel validation error format (422)
        $response->assertStatus(422)
            ->assertJsonStructure([
                'message',
                'errors' => [
                    'requested_role'
                ]
            ]);
    }

    public function test_status_endpoint_contract()
    {
        JournalMembershipApplication::create([
            'user_id' => $this->user->id,
            'journal_id' => $this->journal->id,
            'requested_role' => 'reviewer',
            'status' => JournalMembershipApplication::STATUS_REJECTED,
            'reviewer_note' => 'Not enough experience',
        ]);

        $response = $this->actingAs($this->user, 'api')->getJson('/api/journals/' . $this->journal->slug . '/membership-application/status');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'status' => [
                        'id', 'journal_id', 'requested_role', 'status', 'submitted_at', 'reviewed_at', 'reviewer_note'
                    ]
                ]
            ]);
    }

    public function test_membership_and_capability_remain_separate_contracts()
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

        $membershipResponse = $this->actingAs($this->user, 'api')->getJson('/api/journals/' . $this->journal->slug . '/membership');
        $membershipResponse->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'membership' => [
                        'journal_id', 'role', 'status', 'created_at'
                    ]
                ]
            ]);

        $capabilityResponse = $this->actingAs($this->user, 'api')->getJson('/api/journals/' . $this->journal->slug . '/reviewer-capability');
        $capabilityResponse->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'capability' => [
                        'available_for_review', 'max_reviews_per_month', 'years_of_experience', 'previous_experience'
                    ]
                ]
            ]);
    }

    public function test_user_cannot_inject_privileged_fields_during_draft_creation()
    {
        $data = [
            'requested_role' => 'reviewer',
            'academic_type' => 'researcher',
            'status' => 'approved', // attempt to inject
            'reviewed_by' => 999, // attempt to inject
        ];

        $response = $this->actingAs($this->user, 'api')->postJson('/api/journals/' . $this->journal->slug . '/membership-application', $data);

        $response->assertStatus(201)
            ->assertJsonPath('data.application.status', JournalMembershipApplication::STATUS_DRAFT);
        
        $app = JournalMembershipApplication::where('user_id', $this->user->id)->first();
        $this->assertEquals(JournalMembershipApplication::STATUS_DRAFT, $app->status);
        $this->assertNull($app->reviewed_by);
    }
}
