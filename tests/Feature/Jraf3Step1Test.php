<?php

namespace Tests\Feature;

use App\Models\Institution;
use App\Models\Journal;
use App\Models\JournalMembership;
use App\Models\JournalMembershipApplication;
use App\Models\JournalReviewerCapability;
use App\Models\User;
use App\Services\JournalMembershipApplicationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Jraf3Step1Test extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
        $this->journal = Journal::create([
            'title' => 'Test Journal',
            'slug' => 'test-journal',
            'status' => 'active',
            'user_id' => $this->user->id,
            'description' => 'Test description',
        ]);
        $this->admin = User::factory()->create(['is_admin' => 1]);
        $this->institution = Institution::create([
            'name' => 'Test Institution',
            'is_active' => true,
            'source_id' => '123'
        ]);
    }

    public function test_service_creates_draft_and_syncs_academic_profile()
    {
        $service = new JournalMembershipApplicationService();
        
        $data = [
            'journal_id' => $this->journal->id,
            'requested_role' => 'reviewer',
            'academic_type' => 'researcher',
            'highest_degree' => 'PhD',
            'academic_position' => 'Professor',
            'institution_type' => 'university',
            'institution_id' => $this->institution->id,
            'institution' => 'Test Institution',
            'department' => 'Computer Science',
            'country' => 'Indonesia',
            'biography' => 'Test bio',
            'research_interests' => 'AI',
            'institutional_email' => 'test@test.edu',
            'orcid' => '0000-0002-1825-0097',
            'recruitment_source' => 'self',
            'declarations' => ['agree' => true],
        ];

        $app = $service->createDraft($this->user, $data);

        $this->assertEquals(JournalMembershipApplication::STATUS_DRAFT, $app->status);
        $this->assertEquals('reviewer', $app->requested_role);
        $this->assertEquals('self', $app->recruitment_source);
        $this->assertEquals(['agree' => true], $app->declarations);

        $profile = $this->user->academicProfile;
        $this->assertNotNull($profile);
        $this->assertEquals('researcher', $profile->academic_type);
        $this->assertEquals('PhD', $profile->highest_degree);
    }

    public function test_service_prevents_duplicate_active_application()
    {
        $service = new JournalMembershipApplicationService();
        
        $data = [
            'journal_id' => $this->journal->id,
            'requested_role' => 'reviewer',
            'academic_type' => 'researcher',
        ];

        $service->createDraft($this->user, $data);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('You already have an active application for this role in this journal.');
        
        $service->createDraft($this->user, $data);
    }

    public function test_approved_reviewer_application_creates_reviewer_capability()
    {
        $app = JournalMembershipApplication::create([
            'user_id' => $this->user->id,
            'journal_id' => $this->journal->id,
            'requested_role' => 'reviewer',
            'status' => JournalMembershipApplication::STATUS_SUBMITTED,
        ]);

        $response = $this->actingAs($this->admin)->post(route('admin.membership-verifications.updateStatus', $app->id), [
            'status' => JournalMembershipApplication::STATUS_APPROVED,
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertEquals(JournalMembershipApplication::STATUS_APPROVED, $app->fresh()->status);

        $membership = JournalMembership::where('user_id', $this->user->id)
            ->where('journal_id', $this->journal->id)
            ->first();
            
        $this->assertNotNull($membership);
        $this->assertEquals('reviewer', $membership->role);

        $capability = JournalReviewerCapability::where('journal_membership_id', $membership->id)->first();
        $this->assertNotNull($capability);
        $this->assertTrue($capability->available_for_review);
    }

    public function test_approved_member_application_does_not_create_reviewer_capability()
    {
        $app = JournalMembershipApplication::create([
            'user_id' => $this->user->id,
            'journal_id' => $this->journal->id,
            'requested_role' => 'member',
            'status' => JournalMembershipApplication::STATUS_SUBMITTED,
        ]);

        $response = $this->actingAs($this->admin)->post(route('admin.membership-verifications.updateStatus', $app->id), [
            'status' => JournalMembershipApplication::STATUS_APPROVED,
        ]);

        $membership = JournalMembership::where('user_id', $this->user->id)
            ->where('journal_id', $this->journal->id)
            ->first();
            
        $this->assertNotNull($membership);
        $this->assertEquals('member', $membership->role);

        $capability = JournalReviewerCapability::where('journal_membership_id', $membership->id)->first();
        $this->assertNull($capability);
    }
}
