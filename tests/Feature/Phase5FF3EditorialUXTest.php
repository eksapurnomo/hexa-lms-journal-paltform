<?php

namespace Tests\Feature;

use App\Models\Journal;
use App\Models\ReviewAssignment;
use App\Models\ReviewRound;
use App\Models\Submission;
use App\Models\SubmissionRevision;
use App\Models\SubmissionFile;
use App\Models\JournalMembership;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Phase5FF3EditorialUXTest extends TestCase
{
    use RefreshDatabase;

    private function makeJournal(): Journal
    {
        return Journal::create([
            'title'  => 'Test Journal',
            'slug'   => \Illuminate\Support\Str::uuid(),
            'status' => 'active',
        ]);
    }

    private function makeOwner(Journal $journal): User
    {
        $owner = User::factory()->create();
        JournalMembership::create([
            'journal_id' => $journal->id,
            'user_id'    => $owner->id,
            'role'       => 'owner',
            'status'     => 'active',
        ]);
        return $owner;
    }

    private function makeEditor(Journal $journal): User
    {
        $editor = User::factory()->create();
        JournalMembership::create([
            'journal_id' => $journal->id,
            'user_id'    => $editor->id,
            'role'       => 'editor',
            'status'     => 'active',
        ]);
        return $editor;
    }

    private function makeReviewer(Journal $journal): User
    {
        $reviewer = User::factory()->create();
        JournalMembership::create([
            'journal_id' => $journal->id,
            'user_id'    => $reviewer->id,
            'role'       => 'reviewer',
            'status'     => 'active',
        ]);
        return $reviewer;
    }

    private function makeSubmission(Journal $journal, User $creator, string $status = 'submitted'): Submission
    {
        return Submission::forceCreate([
            'created_by' => $creator->id,
            'journal_id' => $journal->id,
            'title'      => 'Test Submission',
            'status'     => $status,
        ]);
    }

    // 1. Authorized editorial user (owner) can access editorial submission list
    public function test_owner_can_access_editorial_list()
    {
        $journal = $this->makeJournal();
        $owner = $this->makeOwner($journal);
        $author = User::factory()->create();
        $this->makeSubmission($journal, $author, 'submitted');

        $response = $this->actingAs($owner)->getJson('/api/editorial/submissions');
        $response->assertStatus(200);
        $response->assertJsonStructure(['data']);
    }

    // 2. Unauthorized user cannot access editorial submission data
    public function test_non_editorial_user_cannot_access_editorial_detail()
    {
        $journal = $this->makeJournal();
        $author = User::factory()->create();
        $submission = $this->makeSubmission($journal, $author, 'submitted');

        $response = $this->actingAs($author)->getJson("/api/editorial/submissions/{$submission->id}");
        $response->assertStatus(403);
    }

    // 3. Revision hierarchy is returned correctly
    public function test_editorial_detail_returns_revision_hierarchy()
    {
        $journal = $this->makeJournal();
        $owner = $this->makeOwner($journal);
        $author = User::factory()->create();
        $submission = $this->makeSubmission($journal, $author, 'submitted');

        $revision = SubmissionRevision::create([
            'submission_id' => $submission->id,
            'version_number' => 1,
        ]);

        SubmissionFile::create([
            'submission_revision_id' => $revision->id,
            'original_name' => 'paper.pdf',
            'file_path' => 'path/paper.pdf',
            'size' => 100,
            'disk' => 'local',
            'mime_type' => 'application/pdf',
        ]);

        $response = $this->actingAs($owner)->getJson("/api/editorial/submissions/{$submission->id}");
        $response->assertStatus(200);
        $response->assertJsonPath('data.revisions.0.version_number', 1);
        $response->assertJsonPath('data.revisions.0.files.0.original_name', 'paper.pdf');
    }

    // 4. Files are attached to the correct revision
    public function test_files_are_attached_to_correct_revision()
    {
        $journal = $this->makeJournal();
        $owner = $this->makeOwner($journal);
        $author = User::factory()->create();
        $submission = $this->makeSubmission($journal, $author, 'submitted');

        $rev1 = SubmissionRevision::create(['submission_id' => $submission->id, 'version_number' => 1]);
        $rev2 = SubmissionRevision::create(['submission_id' => $submission->id, 'version_number' => 2]);

        SubmissionFile::create(['submission_revision_id' => $rev1->id, 'original_name' => 'v1.pdf', 'file_path' => 'v1.pdf', 'size' => 100, 'disk' => 'local', 'mime_type' => 'application/pdf']);
        SubmissionFile::create(['submission_revision_id' => $rev2->id, 'original_name' => 'v2.pdf', 'file_path' => 'v2.pdf', 'size' => 100, 'disk' => 'local', 'mime_type' => 'application/pdf']);

        $response = $this->actingAs($owner)->getJson("/api/editorial/submissions/{$submission->id}");
        $response->assertStatus(200);

        $revisions = $response->json('data.revisions');
        $this->assertCount(2, $revisions);
        $this->assertEquals('v1.pdf', $revisions[0]['files'][0]['original_name']);
        $this->assertEquals('v2.pdf', $revisions[1]['files'][0]['original_name']);
    }

    // 5. Review rounds are attached to correct revision
    public function test_review_rounds_attached_to_correct_revision()
    {
        $journal = $this->makeJournal();
        $owner = $this->makeOwner($journal);
        $author = User::factory()->create();
        $submission = $this->makeSubmission($journal, $author, 'review_pending');

        $revision = SubmissionRevision::create(['submission_id' => $submission->id, 'version_number' => 1]);
        ReviewRound::create(['submission_revision_id' => $revision->id, 'round_number' => 1]);

        $response = $this->actingAs($owner)->getJson("/api/editorial/submissions/{$submission->id}");
        $response->assertStatus(200);
        $response->assertJsonPath('data.revisions.0.review_rounds.0.round_number', 1);
    }

    // 6. Assignments are distinguishable from peer reviews
    public function test_assignments_and_peer_reviews_are_distinguishable()
    {
        $journal = $this->makeJournal();
        $owner = $this->makeOwner($journal);
        $reviewer = $this->makeReviewer($journal);
        $author = User::factory()->create();
        $submission = $this->makeSubmission($journal, $author, 'review_pending');
        $submission->forceFill(['editor_id' => $owner->id])->save();

        $revision = SubmissionRevision::create(['submission_id' => $submission->id, 'version_number' => 1]);
        $round = ReviewRound::create(['submission_revision_id' => $revision->id, 'round_number' => 1]);
        ReviewAssignment::create([
            'review_round_id' => $round->id,
            'reviewer_id'     => $reviewer->id,
            'assigned_by'     => $owner->id,
            'status'          => 'assigned',
            'review_mode'     => 'single_blind',
        ]);

        $response = $this->actingAs($owner)->getJson("/api/editorial/submissions/{$submission->id}");
        $response->assertStatus(200);

        $assignment = $response->json('data.revisions.0.review_rounds.0.assignments.0');
        $this->assertArrayHasKey('status', $assignment);
        $this->assertArrayHasKey('review_mode', $assignment);
        // peer_review is null since not yet submitted
        $this->assertNull($assignment['peer_review']);
    }

    // 7. Reviewer identity is exposed to editors (editorial context)
    public function test_reviewer_identity_is_exposed_to_editor()
    {
        $journal = $this->makeJournal();
        $owner = $this->makeOwner($journal);
        $reviewer = $this->makeReviewer($journal);
        $author = User::factory()->create();
        $submission = $this->makeSubmission($journal, $author, 'review_pending');
        $submission->forceFill(['editor_id' => $owner->id])->save();

        $revision = SubmissionRevision::create(['submission_id' => $submission->id, 'version_number' => 1]);
        $round = ReviewRound::create(['submission_revision_id' => $revision->id, 'round_number' => 1]);
        ReviewAssignment::create([
            'review_round_id' => $round->id,
            'reviewer_id'     => $reviewer->id,
            'assigned_by'     => $owner->id,
            'status'          => 'assigned',
            'review_mode'     => 'single_blind',
        ]);

        $response = $this->actingAs($owner)->getJson("/api/editorial/submissions/{$submission->id}");
        $response->assertStatus(200);

        $assignment = $response->json('data.revisions.0.review_rounds.0.assignments.0');
        $this->assertNotNull($assignment['reviewer']);
        $this->assertEquals($reviewer->name, $assignment['reviewer']['name']);
        $this->assertEquals($reviewer->email, $assignment['reviewer']['email']);
    }

    // 8. updateStatus — valid transition works
    public function test_update_status_valid_transition()
    {
        $journal = $this->makeJournal();
        $owner = $this->makeOwner($journal);
        $author = User::factory()->create();
        $submission = $this->makeSubmission($journal, $author, 'submitted');
        $submission->forceFill(['editor_id' => $owner->id])->save();

        $response = $this->actingAs($owner)->patchJson("/api/editorial/submissions/{$submission->id}/status", [
            'status' => 'editorial_assessment',
        ]);
        $response->assertStatus(200);
        $this->assertDatabaseHas('submissions', ['id' => $submission->id, 'status' => 'editorial_assessment']);
    }

    // 9. updateStatus — invalid transition is rejected
    public function test_update_status_invalid_transition_rejected()
    {
        $journal = $this->makeJournal();
        $owner = $this->makeOwner($journal);
        $author = User::factory()->create();
        $submission = $this->makeSubmission($journal, $author, 'submitted');
        $submission->forceFill(['editor_id' => $owner->id])->save();

        // Cannot jump from 'submitted' directly to 'accepted'
        $response = $this->actingAs($owner)->patchJson("/api/editorial/submissions/{$submission->id}/status", [
            'status' => 'accepted',
        ]);
        $response->assertStatus(422);
        $this->assertDatabaseHas('submissions', ['id' => $submission->id, 'status' => 'submitted']);
    }

    // 10. Unauthorized user cannot transition status
    public function test_non_editorial_user_cannot_transition_status()
    {
        $journal = $this->makeJournal();
        $author = User::factory()->create();
        $submission = $this->makeSubmission($journal, $author, 'submitted');

        $response = $this->actingAs($author)->patchJson("/api/editorial/submissions/{$submission->id}/status", [
            'status' => 'editorial_assessment',
        ]);
        $response->assertStatus(403);
    }

    // 11. Editorial decisions are read-only — cannot update existing decision
    public function test_editorial_decision_is_read_only_once_recorded()
    {
        $journal = $this->makeJournal();
        $owner = $this->makeOwner($journal);
        $author = User::factory()->create();
        $submission = $this->makeSubmission($journal, $author, 'review_pending');
        $submission->forceFill(['editor_id' => $owner->id])->save();

        $revision = SubmissionRevision::create(['submission_id' => $submission->id, 'version_number' => 1]);
        $round = ReviewRound::create(['submission_revision_id' => $revision->id, 'round_number' => 1]);

        // Record first decision
        $this->actingAs($owner)->postJson("/api/editorial/submissions/{$submission->id}/rounds/{$round->id}/decision", [
            'decision' => 'accept',
        ])->assertStatus(200);

        // Attempt second decision on same round — must be rejected
        $response = $this->actingAs($owner)->postJson("/api/editorial/submissions/{$submission->id}/rounds/{$round->id}/decision", [
            'decision' => 'reject',
        ]);
        $response->assertStatus(422);
    }
}
