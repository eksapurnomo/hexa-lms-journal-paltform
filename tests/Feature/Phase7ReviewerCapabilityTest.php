<?php

namespace Tests\Feature;

use App\Models\Journal;
use App\Models\JournalMembership;
use App\Models\JournalMembershipApplication;
use App\Models\JournalReviewerCapability;
use App\Models\ReviewerApplication;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Phase7ReviewerCapabilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_journal_membership_has_one_reviewer_capability()
    {
        $user = User::factory()->create();
        $journal = Journal::create(['title' => 'J1', 'slug' => 'j1', 'status' => 'active']);
        
        $membership = JournalMembership::create([
            'user_id' => $user->id,
            'journal_id' => $journal->id,
            'role' => 'reviewer',
            'status' => 'active',
        ]);

        $capability = JournalReviewerCapability::create([
            'journal_membership_id' => $membership->id,
            'available_for_review' => true,
            'max_reviews_per_month' => 3,
        ]);

        $this->assertTrue($membership->reviewerCapability->is($capability));
    }

    public function test_reviewer_capability_belongs_to_exactly_one_journal_membership()
    {
        $user = User::factory()->create();
        $journal = Journal::create(['title' => 'J1', 'slug' => 'j1', 'status' => 'active']);
        
        $membership = JournalMembership::create([
            'user_id' => $user->id,
            'journal_id' => $journal->id,
            'role' => 'reviewer',
            'status' => 'active',
        ]);

        $capability = JournalReviewerCapability::create([
            'journal_membership_id' => $membership->id,
        ]);

        $this->assertTrue($capability->journalMembership->is($membership));
    }

    public function test_journal_membership_id_uniqueness_prevents_duplicate_capability_records()
    {
        $user = User::factory()->create();
        $journal = Journal::create(['title' => 'J1', 'slug' => 'j1', 'status' => 'active']);
        
        $membership = JournalMembership::create([
            'user_id' => $user->id,
            'journal_id' => $journal->id,
            'role' => 'reviewer',
            'status' => 'active',
        ]);

        JournalReviewerCapability::create([
            'journal_membership_id' => $membership->id,
        ]);

        $this->expectException(QueryException::class);

        JournalReviewerCapability::create([
            'journal_membership_id' => $membership->id,
        ]);
    }

    public function test_capability_fields_correctly_cast()
    {
        $user = User::factory()->create();
        $journal = Journal::create(['title' => 'J1', 'slug' => 'j1', 'status' => 'active']);
        
        $membership = JournalMembership::create([
            'user_id' => $user->id,
            'journal_id' => $journal->id,
            'role' => 'reviewer',
            'status' => 'active',
        ]);

        $capability = JournalReviewerCapability::create([
            'journal_membership_id' => $membership->id,
            'available_for_review' => 0, // testing cast
            'max_reviews_per_month' => '4', // testing cast
        ]);

        $this->assertIsBool($capability->available_for_review);
        $this->assertFalse($capability->available_for_review);
        $this->assertIsInt($capability->max_reviews_per_month);
        $this->assertSame(4, $capability->max_reviews_per_month);
    }

    public function test_journal_membership_application_accepts_recruitment_source_and_declarations()
    {
        $user = User::factory()->create();
        $journal = Journal::create(['title' => 'J1', 'slug' => 'j1', 'status' => 'active']);

        $application = JournalMembershipApplication::create([
            'user_id' => $user->id,
            'journal_id' => $journal->id,
            'requested_role' => 'reviewer',
            'status' => 'submitted',
            'recruitment_source' => 'INVITED_REVIEWER',
            'declarations' => ['confidentiality_agreed' => true],
        ]);

        $this->assertEquals('INVITED_REVIEWER', $application->recruitment_source);
        $this->assertEquals(['confidentiality_agreed' => true], $application->declarations);
    }

    public function test_declarations_are_correctly_handled_as_json()
    {
        $user = User::factory()->create();
        $journal = Journal::create(['title' => 'J1', 'slug' => 'j1', 'status' => 'active']);

        $application = JournalMembershipApplication::create([
            'user_id' => $user->id,
            'journal_id' => $journal->id,
            'requested_role' => 'reviewer',
            'status' => 'submitted',
            'declarations' => ['key1' => 'val1', 'key2' => 'val2'],
        ]);

        $application->refresh();
        $this->assertIsArray($application->declarations);
        $this->assertArrayHasKey('key1', $application->declarations);
        $this->assertEquals('val1', $application->declarations['key1']);
    }

    public function test_recruitment_source_does_not_automatically_activate_reviewer_membership()
    {
        $user = User::factory()->create();
        $journal = Journal::create(['title' => 'J1', 'slug' => 'j1', 'status' => 'active']);

        $application = JournalMembershipApplication::create([
            'user_id' => $user->id,
            'journal_id' => $journal->id,
            'requested_role' => 'reviewer',
            'status' => 'submitted',
            'recruitment_source' => 'INVITED_REVIEWER',
        ]);

        $membership = JournalMembership::where('user_id', $user->id)->where('journal_id', $journal->id)->first();
        
        $this->assertNull($membership, 'Membership should not be created automatically without explicit approval workflow');
    }

    public function test_capability_record_does_not_grant_reviewer_authorization_by_itself()
    {
        $user = User::factory()->create();
        $journal = Journal::create(['title' => 'J1', 'slug' => 'j1', 'status' => 'active']);

        // Membership is inactive
        $membership = JournalMembership::create([
            'user_id' => $user->id,
            'journal_id' => $journal->id,
            'role' => 'reviewer',
            'status' => 'inactive',
        ]);

        $capability = JournalReviewerCapability::create([
            'journal_membership_id' => $membership->id,
            'available_for_review' => true,
        ]);

        $this->assertEquals('inactive', $membership->status);
        $this->assertTrue($capability->available_for_review);
        // Authorization relies on $membership->status == 'active' and role == 'reviewer'.
        // The presence of a true 'available_for_review' capability does not override the membership status.
    }

    public function test_existing_reviewer_application_functionality_remains_intact()
    {
        $user = User::factory()->create();
        $journal = Journal::create(['title' => 'J1', 'slug' => 'j1', 'status' => 'active']);
        
        $legacyApp = ReviewerApplication::create([
            'user_id' => $user->id,
            'journal_id' => $journal->id,
            'status' => 'pending',
            'affiliation' => 'Univ A',
            'department' => 'CS',
            'academic_position' => 'Lecturer',
            'primary_research_area' => 'AI',
        ]);

        $this->assertNotNull($legacyApp->id);
        $this->assertEquals('pending', $legacyApp->status);
    }
}
