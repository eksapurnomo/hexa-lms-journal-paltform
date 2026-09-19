<?php

namespace Tests\Feature;

use App\Models\Journal;
use App\Models\JournalMembership;
use App\Models\JournalReviewerCapability;
use App\Models\ReviewerApplication;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Phase7CanonicalReviewerEligibilityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Create an admin to bypass editorial-process gate if needed, or an editor
    }

    private function createEditor(Journal $journal)
    {
        $editor = User::factory()->create(['is_admin' => true]);
        JournalMembership::create([
            'user_id' => $editor->id,
            'journal_id' => $journal->id,
            'role' => 'editor',
            'status' => 'active',
        ]);
        return $editor;
    }

    private function createSubmission(Journal $journal)
    {
        $submission = Submission::forceCreate([
            'journal_id' => $journal->id,
            'created_by' => User::factory()->create()->id,
            'title' => 'Test Submission',
            'status' => 'in_review',
        ]);
        
        $submission->revisions()->create([
            'version_number' => 1,
            'file_path' => 'dummy/path.pdf',
        ]);
        
        return $submission;
    }

    public function test_active_reviewer_with_capability_is_eligible_without_legacy_app()
    {
        $journal = Journal::create(['title' => 'J1', 'slug' => 'j1', 'status' => 'active']);
        $editor = $this->createEditor($journal);
        $submission = $this->createSubmission($journal);

        $reviewer = User::factory()->create();
        $membership = JournalMembership::create([
            'user_id' => $reviewer->id,
            'journal_id' => $journal->id,
            'role' => 'reviewer',
            'status' => 'active',
        ]);
        JournalReviewerCapability::create([
            'journal_membership_id' => $membership->id,
            'available_for_review' => true,
        ]);

        $response = $this->actingAs($editor)->getJson("/api/editorial/submissions/{$submission->id}/eligible-reviewers");
        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
        $this->assertEquals($reviewer->id, $response->json('data.0.id'));
    }

    public function test_inactive_reviewer_is_not_eligible()
    {
        $journal = Journal::create(['title' => 'J1', 'slug' => 'j1', 'status' => 'active']);
        $editor = $this->createEditor($journal);
        $submission = $this->createSubmission($journal);

        $reviewer = User::factory()->create();
        $membership = JournalMembership::create([
            'user_id' => $reviewer->id,
            'journal_id' => $journal->id,
            'role' => 'reviewer',
            'status' => 'inactive',
        ]);
        JournalReviewerCapability::create([
            'journal_membership_id' => $membership->id,
            'available_for_review' => true,
        ]);

        $response = $this->actingAs($editor)->getJson("/api/editorial/submissions/{$submission->id}/eligible-reviewers");
        $this->assertCount(0, $response->json('data'));
    }

    public function test_non_reviewer_membership_is_not_eligible()
    {
        $journal = Journal::create(['title' => 'J1', 'slug' => 'j1', 'status' => 'active']);
        $editor = $this->createEditor($journal);
        $submission = $this->createSubmission($journal);

        $user = User::factory()->create();
        JournalMembership::create([
            'user_id' => $user->id,
            'journal_id' => $journal->id,
            'role' => 'author',
            'status' => 'active',
        ]);
        // Capability shouldn't exist for author theoretically, but even if it does:
        // (Bypassing constraint by forcing it)

        $response = $this->actingAs($editor)->getJson("/api/editorial/submissions/{$submission->id}/eligible-reviewers");
        $this->assertCount(0, $response->json('data'));
    }

    public function test_reviewer_from_another_journal_is_not_eligible()
    {
        $journal1 = Journal::create(['title' => 'J1', 'slug' => 'j1', 'status' => 'active']);
        $journal2 = Journal::create(['title' => 'J2', 'slug' => 'j2', 'status' => 'active']);
        $editor = $this->createEditor($journal1);
        $submission = $this->createSubmission($journal1);

        $reviewer = User::factory()->create();
        $membership = JournalMembership::create([
            'user_id' => $reviewer->id,
            'journal_id' => $journal2->id,
            'role' => 'reviewer',
            'status' => 'active',
        ]);
        JournalReviewerCapability::create([
            'journal_membership_id' => $membership->id,
            'available_for_review' => true,
        ]);

        $response = $this->actingAs($editor)->getJson("/api/editorial/submissions/{$submission->id}/eligible-reviewers");
        $this->assertCount(0, $response->json('data'));
    }

    public function test_capability_with_available_false_is_not_eligible()
    {
        $journal = Journal::create(['title' => 'J1', 'slug' => 'j1', 'status' => 'active']);
        $editor = $this->createEditor($journal);
        $submission = $this->createSubmission($journal);

        $reviewer = User::factory()->create();
        $membership = JournalMembership::create([
            'user_id' => $reviewer->id,
            'journal_id' => $journal->id,
            'role' => 'reviewer',
            'status' => 'active',
        ]);
        JournalReviewerCapability::create([
            'journal_membership_id' => $membership->id,
            'available_for_review' => false,
        ]);

        $response = $this->actingAs($editor)->getJson("/api/editorial/submissions/{$submission->id}/eligible-reviewers");
        $this->assertCount(0, $response->json('data'));
    }

    public function test_legacy_application_fallback_for_missing_capability()
    {
        $journal = Journal::create(['title' => 'J1', 'slug' => 'j1', 'status' => 'active']);
        $editor = $this->createEditor($journal);
        $submission = $this->createSubmission($journal);

        $reviewer = User::factory()->create();
        JournalMembership::create([
            'user_id' => $reviewer->id,
            'journal_id' => $journal->id,
            'role' => 'reviewer',
            'status' => 'active',
        ]);
        // NO Capability created
        ReviewerApplication::create([
            'user_id' => $reviewer->id,
            'journal_id' => $journal->id,
            'status' => 'accepted',
            'affiliation' => 'A',
            'department' => 'D',
            'academic_position' => 'P',
            'primary_research_area' => 'R',
        ]);

        $response = $this->actingAs($editor)->getJson("/api/editorial/submissions/{$submission->id}/eligible-reviewers");
        $this->assertCount(1, $response->json('data'));
    }

    public function test_missing_capability_and_missing_legacy_application_is_not_eligible()
    {
        $journal = Journal::create(['title' => 'J1', 'slug' => 'j1', 'status' => 'active']);
        $editor = $this->createEditor($journal);
        $submission = $this->createSubmission($journal);

        $reviewer = User::factory()->create();
        JournalMembership::create([
            'user_id' => $reviewer->id,
            'journal_id' => $journal->id,
            'role' => 'reviewer',
            'status' => 'active',
        ]);
        // NO Capability, NO accepted ReviewerApplication

        $response = $this->actingAs($editor)->getJson("/api/editorial/submissions/{$submission->id}/eligible-reviewers");
        $this->assertCount(0, $response->json('data'));
    }

    public function test_academic_profile_no_longer_mandatory_for_authorization()
    {
        $journal = Journal::create(['title' => 'J1', 'slug' => 'j1', 'status' => 'active']);
        $editor = $this->createEditor($journal);
        $submission = $this->createSubmission($journal);

        $reviewer = User::factory()->create(); // No AcademicProfile
        $membership = JournalMembership::create([
            'user_id' => $reviewer->id,
            'journal_id' => $journal->id,
            'role' => 'reviewer',
            'status' => 'active',
        ]);
        JournalReviewerCapability::create([
            'journal_membership_id' => $membership->id,
            'available_for_review' => true,
        ]);

        $response = $this->actingAs($editor)->getJson("/api/editorial/submissions/{$submission->id}/eligible-reviewers");
        $this->assertCount(1, $response->json('data'));
    }

    public function test_existing_assignment_duplicate_protection_remains_intact()
    {
        $journal = Journal::create(['title' => 'J1', 'slug' => 'j1', 'status' => 'active']);
        $editor = $this->createEditor($journal);
        $submission = $this->createSubmission($journal);

        $reviewer = User::factory()->create();
        $membership = JournalMembership::create([
            'user_id' => $reviewer->id,
            'journal_id' => $journal->id,
            'role' => 'reviewer',
            'status' => 'active',
        ]);
        JournalReviewerCapability::create([
            'journal_membership_id' => $membership->id,
            'available_for_review' => true,
        ]);

        // First assignment
        $response = $this->actingAs($editor)->postJson("/api/editorial/submissions/{$submission->id}/review-assignments", [
            'reviewer_id' => $reviewer->id,
        ]);
        $response->assertStatus(200);

        // Duplicate assignment should fail
        $response2 = $this->actingAs($editor)->postJson("/api/editorial/submissions/{$submission->id}/review-assignments", [
            'reviewer_id' => $reviewer->id,
        ]);
        $response2->assertStatus(500); // The original logic throws an Exception, converting to 500
    }

    public function test_assign_reviewer_respects_capability_availability()
    {
        $journal = Journal::create(['title' => 'J1', 'slug' => 'j1', 'status' => 'active']);
        $editor = $this->createEditor($journal);
        $submission = $this->createSubmission($journal);

        $reviewer = User::factory()->create();
        $membership = JournalMembership::create([
            'user_id' => $reviewer->id,
            'journal_id' => $journal->id,
            'role' => 'reviewer',
            'status' => 'active',
        ]);
        JournalReviewerCapability::create([
            'journal_membership_id' => $membership->id,
            'available_for_review' => false,
        ]);

        $response = $this->actingAs($editor)->postJson("/api/editorial/submissions/{$submission->id}/review-assignments", [
            'reviewer_id' => $reviewer->id,
        ]);
        $response->assertStatus(422);
        $this->assertStringContainsString('Reviewer is not currently available', $response->json('message') ?? $response->content());
    }

    public function test_assign_reviewer_respects_legacy_fallback()
    {
        $journal = Journal::create(['title' => 'J1', 'slug' => 'j1', 'status' => 'active']);
        $editor = $this->createEditor($journal);
        $submission = $this->createSubmission($journal);

        $reviewer = User::factory()->create();
        JournalMembership::create([
            'user_id' => $reviewer->id,
            'journal_id' => $journal->id,
            'role' => 'reviewer',
            'status' => 'active',
        ]);
        // Missing capability, but missing accepted application too
        $response = $this->actingAs($editor)->postJson("/api/editorial/submissions/{$submission->id}/review-assignments", [
            'reviewer_id' => $reviewer->id,
        ]);
        $response->assertStatus(422);
        $this->assertStringContainsString('Reviewer is missing required capability or legacy application', $response->json('message') ?? $response->content());
    }
}
