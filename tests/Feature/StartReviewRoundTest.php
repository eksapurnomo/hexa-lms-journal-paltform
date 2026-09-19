<?php

namespace Tests\Feature;

use App\Models\Journal;
use App\Models\PeerReviewPolicy;
use App\Models\Submission;
use App\Models\SubmissionRevision;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StartReviewRoundTest extends TestCase
{
    use RefreshDatabase;

    protected $owner;
    protected $editor;
    protected $unassignedEditor;
    protected $author;
    protected $journal;
    protected $submission;

    protected function setUp(): void
    {
        parent::setUp();

        $this->owner = User::factory()->create();
        $this->editor = User::factory()->create();
        $this->unassignedEditor = User::factory()->create();
        $this->author = User::factory()->create();

        $this->journal = Journal::forceCreate([
            'title' => 'Test Journal',
            'slug' => uniqid(),
            'status' => 'active'
        ]);
        
        $this->journal->memberships()->create([
            'user_id' => $this->owner->id,
            'role' => 'owner',
            'status' => 'active',
        ]);
        
        $this->journal->peerReviewPolicy()->create([
            'review_model' => 'double_blind',
            'minimum_reviewers' => 2,
            'target_reviewers' => 3,
            'maximum_reviewers' => 4,
        ]);

        $this->journal->memberships()->create([
            'user_id' => $this->editor->id,
            'role' => 'editor',
            'status' => 'active',
        ]);
        
        $this->journal->memberships()->create([
            'user_id' => $this->unassignedEditor->id,
            'role' => 'editor',
            'status' => 'active',
        ]);

        $this->submission = Submission::forceCreate([
            'journal_id' => $this->journal->id,
            'editor_id' => $this->editor->id,
            'created_by' => $this->author->id,
            'title' => 'Test Submission',
            'abstract' => 'Test Abstract',
            'status' => Submission::STATUS_EDITORIAL_ASSESSMENT,
        ]);

        $this->submission->revisions()->create([
            'version_number' => 1,
        ]);
    }

    public function test_authorized_editor_can_start_review_round()
    {
        $response = $this->actingAs($this->editor)->postJson("/api/editorial/submissions/{$this->submission->id}/rounds");

        $response->assertStatus(200);

        $this->assertDatabaseHas('review_rounds', [
            'submission_revision_id' => $this->submission->revisions->first()->id,
            'round_number' => 1,
            'review_model' => 'double_blind',
            'minimum_reviewers' => 2,
            'target_reviewers' => 3,
            'maximum_reviewers' => 4,
        ]);

        // Assert submission status updated to review_pending
        $this->assertDatabaseHas('submissions', [
            'id' => $this->submission->id,
            'status' => \App\Models\Submission::STATUS_REVIEW_PENDING,
        ]);
    }

    public function test_unassigned_editor_cannot_start_review_round()
    {
        $response = $this->actingAs($this->unassignedEditor)->postJson("/api/editorial/submissions/{$this->submission->id}/rounds");

        $response->assertStatus(403);
    }

    public function test_author_cannot_start_review_round()
    {
        $response = $this->actingAs($this->author)->postJson("/api/editorial/submissions/{$this->submission->id}/rounds");

        $response->assertStatus(403);
    }

    public function test_cannot_create_duplicate_active_round()
    {
        // Start first round
        $this->actingAs($this->editor)->postJson("/api/editorial/submissions/{$this->submission->id}/rounds")->assertStatus(200);

        // Attempt second round before decision is made
        $response = $this->actingAs($this->editor)->postJson("/api/editorial/submissions/{$this->submission->id}/rounds");
        $response->assertStatus(422);
    }

    public function test_policy_snapshot_is_immutable()
    {
        // Start round
        $this->actingAs($this->editor)->postJson("/api/editorial/submissions/{$this->submission->id}/rounds")->assertStatus(200);

        // Change journal policy
        $this->journal->peerReviewPolicy->update([
            'review_model' => 'open',
            'minimum_reviewers' => 1,
            'target_reviewers' => 1,
            'maximum_reviewers' => 2,
        ]);

        // Verify the existing round still holds the old snapshot
        $this->assertDatabaseHas('review_rounds', [
            'submission_revision_id' => $this->submission->revisions->first()->id,
            'round_number' => 1,
            'review_model' => 'double_blind',
            'minimum_reviewers' => 2,
            'target_reviewers' => 3,
            'maximum_reviewers' => 4,
        ]);
    }
}
