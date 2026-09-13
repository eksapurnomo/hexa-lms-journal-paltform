<?php

namespace Tests\Feature;

use App\Models\Journal;
use App\Models\Submission;
use App\Models\SubmissionRevision;
use App\Models\SubmissionFile;
use App\Models\ReviewRound;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class Phase5FF2AuthorUXTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
    }

    public function test_author_can_get_own_submission()
    {
        $author = User::factory()->create();
        $journal = Journal::create(['title' => 'Test Journal', 'slug' => \Illuminate\Support\Str::uuid(), 'status' => 'active']);
        $submission = Submission::forceCreate([
            'created_by' => $author->id,
            'journal_id' => $journal->id,
            'title' => 'Test',
            'status' => 'draft',
        ]);

        $response = $this->actingAs($author, 'api')->getJson("/api/submissions/{$submission->id}");
        
        $response->assertStatus(200)
                 ->assertJsonPath('data.id', $submission->id);
    }

    public function test_author_cannot_get_other_author_submission()
    {
        $authorA = User::factory()->create();
        $authorB = User::factory()->create();
        $journal = Journal::create(['title' => 'Test Journal', 'slug' => \Illuminate\Support\Str::uuid(), 'status' => 'active']);
        
        $submissionB = Submission::forceCreate([
            'created_by' => $authorB->id,
            'journal_id' => $journal->id,
            'title' => 'Test',
            'status' => 'draft',
        ]);

        $response = $this->actingAs($authorA, 'api')->getJson("/api/submissions/{$submissionB->id}");
        
        $response->assertStatus(403);
    }

    public function test_author_cannot_upload_file_to_other_author_submission()
    {
        $authorA = User::factory()->create();
        $authorB = User::factory()->create();
        $journal = Journal::create(['title' => 'Test Journal', 'slug' => \Illuminate\Support\Str::uuid(), 'status' => 'active']);
        
        $submissionB = Submission::forceCreate([
            'created_by' => $authorB->id,
            'journal_id' => $journal->id,
            'title' => 'Test',
            'status' => 'draft',
        ]);

        $file = UploadedFile::fake()->create('manuscript.pdf', 100);

        $response = $this->actingAs($authorA, 'api')->postJson("/api/submissions/{$submissionB->id}/files", [
            'file' => $file,
        ]);
        
        $response->assertStatus(403);
    }

    public function test_author_cannot_submit_other_author_submission()
    {
        $authorA = User::factory()->create();
        $authorB = User::factory()->create();
        $journal = Journal::create(['title' => 'Test Journal', 'slug' => \Illuminate\Support\Str::uuid(), 'status' => 'active']);
        
        $submissionB = Submission::forceCreate([
            'created_by' => $authorB->id,
            'journal_id' => $journal->id,
            'title' => 'Test',
            'status' => 'draft',
        ]);

        $response = $this->actingAs($authorA, 'api')->postJson("/api/submissions/{$submissionB->id}/submit");
        
        $response->assertStatus(403);
    }

    public function test_upload_file_does_not_create_new_revision_for_round_without_assignment()
    {
        $author = User::factory()->create();
        $journal = Journal::create(['title' => 'Test Journal', 'slug' => \Illuminate\Support\Str::uuid(), 'status' => 'active']);
        
        $submission = Submission::forceCreate([
            'created_by' => $author->id,
            'journal_id' => $journal->id,
            'title' => 'Test',
            'status' => 'revision_required',
        ]);

        $revision1 = SubmissionRevision::create([
            'submission_id' => $submission->id,
            'version_number' => 1,
        ]);

        ReviewRound::create([
            'submission_revision_id' => $revision1->id,
            'round_number' => 1,
            'status' => 'in_progress',
        ]);

        // Author uploads a new file while in revision_required, but the round has NO assignments
        $file = UploadedFile::fake()->create('revision.pdf', 100);

        $response = $this->actingAs($author, 'api')->postJson("/api/submissions/{$submission->id}/files", [
            'file' => $file,
        ]);

        $response->assertStatus(200);

        // A new revision should NOT be created because the round has no assignments (review hasn't started)
        $this->assertDatabaseMissing('submission_revisions', [
            'submission_id' => $submission->id,
            'version_number' => 2,
        ]);

        // Revision 1 should still be the only revision
        $this->assertDatabaseHas('submission_revisions', [
            'id' => $revision1->id,
            'submission_id' => $submission->id,
            'version_number' => 1,
        ]);
    }

    public function test_upload_file_creates_new_revision_for_round_with_assignment()
    {
        $author = User::factory()->create();
        $journal = Journal::create(['title' => 'Test Journal', 'slug' => \Illuminate\Support\Str::uuid(), 'status' => 'active']);
        
        $submission = Submission::forceCreate([
            'created_by' => $author->id,
            'journal_id' => $journal->id,
            'title' => 'Test',
            'status' => 'revision_required',
        ]);

        $revision1 = SubmissionRevision::create([
            'submission_id' => $submission->id,
            'version_number' => 1,
        ]);

        $round = ReviewRound::create([
            'submission_revision_id' => $revision1->id,
            'round_number' => 1,
            'status' => 'in_progress',
        ]);

        \App\Models\ReviewAssignment::create([
            'review_round_id' => $round->id,
            'reviewer_id' => User::factory()->create()->id,
            'status' => 'assigned',
            'review_mode' => 'single_blind',
            'assigned_by' => User::factory()->create()->id,
        ]);

        // Author uploads a new file while in revision_required, and round HAS assignments
        $file = UploadedFile::fake()->create('revision.pdf', 100);

        $response = $this->actingAs($author, 'api')->postJson("/api/submissions/{$submission->id}/files", [
            'file' => $file,
        ]);

        $response->assertStatus(200);

        // A new revision (version 2) should be created because version 1 is locked by the assignment
        $this->assertDatabaseHas('submission_revisions', [
            'submission_id' => $submission->id,
            'version_number' => 2,
        ]);
    }

    public function test_submit_revision_works_for_round_without_assignment()
    {
        $author = User::factory()->create();
        $journal = Journal::create(['title' => 'Test Journal', 'slug' => \Illuminate\Support\Str::uuid(), 'status' => 'active']);
        
        $submission = Submission::forceCreate([
            'created_by' => $author->id,
            'journal_id' => $journal->id,
            'title' => 'Test',
            'status' => 'revision_required',
        ]);

        $submission->authors()->create([
            'first_name' => 'Test',
            'is_corresponding' => true,
            'sequence' => 1,
        ]);

        $revision1 = SubmissionRevision::create([
            'submission_id' => $submission->id,
            'version_number' => 1,
        ]);

        SubmissionFile::create([
            'submission_revision_id' => $revision1->id,
            'original_name' => 'test.pdf',
            'file_path' => 'path/test.pdf',
            'size' => 100,
            'disk' => 'local',
            'mime_type' => 'application/pdf',
        ]);

        ReviewRound::create([
            'submission_revision_id' => $revision1->id,
            'round_number' => 1,
            'status' => 'in_progress',
        ]);
        // NO assignments on this round!

        // submitRevision should work because the round has no assignments
        $response = $this->actingAs($author, 'api')->postJson("/api/submissions/{$submission->id}/revision");
        
        $response->assertStatus(200);
        
        $this->assertDatabaseHas('submissions', [
            'id' => $submission->id,
            'status' => 'revision_submitted',
        ]);
    }

    public function test_submit_revision_rejected_for_round_with_assignment()
    {
        $author = User::factory()->create();
        $journal = Journal::create(['title' => 'Test Journal', 'slug' => \Illuminate\Support\Str::uuid(), 'status' => 'active']);
        
        $submission = Submission::forceCreate([
            'created_by' => $author->id,
            'journal_id' => $journal->id,
            'title' => 'Test',
            'status' => 'revision_required',
        ]);

        $submission->authors()->create([
            'first_name' => 'Test',
            'is_corresponding' => true,
            'sequence' => 1,
        ]);

        $revision1 = SubmissionRevision::create([
            'submission_id' => $submission->id,
            'version_number' => 1,
        ]);

        SubmissionFile::create([
            'submission_revision_id' => $revision1->id,
            'original_name' => 'test.pdf',
            'file_path' => 'path/test.pdf',
            'size' => 100,
            'disk' => 'local',
            'mime_type' => 'application/pdf',
        ]);

        $round = ReviewRound::create([
            'submission_revision_id' => $revision1->id,
            'round_number' => 1,
            'status' => 'in_progress',
        ]);

        \App\Models\ReviewAssignment::create([
            'review_round_id' => $round->id,
            'reviewer_id' => User::factory()->create()->id,
            'status' => 'assigned',
            'review_mode' => 'single_blind',
            'assigned_by' => User::factory()->create()->id,
        ]);

        // submitRevision should be rejected because the assignment means the revision is locked, 
        // and author hasn't uploaded a new file (which would have created a new unlocked revision).
        $response = $this->actingAs($author, 'api')->postJson("/api/submissions/{$submission->id}/revision");
        
        $response->assertStatus(422)
                 ->assertJsonFragment(['message' => 'You must upload a new manuscript file before submitting the revision.']);
    }

    public function test_author_response_does_not_expose_reviewer_or_editorial_data()
    {
        $author = User::factory()->create();
        $journal = Journal::create(['title' => 'Test Journal', 'slug' => \Illuminate\Support\Str::uuid(), 'status' => 'active']);
        
        $submission = Submission::forceCreate([
            'created_by' => $author->id,
            'journal_id' => $journal->id,
            'title' => 'Test',
            'status' => 'submitted',
        ]);

        $revision = SubmissionRevision::create([
            'submission_id' => $submission->id,
            'version_number' => 1,
        ]);

        ReviewRound::create([
            'submission_revision_id' => $revision->id,
            'round_number' => 1,
            'status' => 'in_progress',
        ]);

        $response = $this->actingAs($author, 'api')->getJson("/api/submissions/{$submission->id}");
        
        $response->assertStatus(200);
        
        // Assert review rounds and editorial events are NOT present in the JSON response
        $response->assertJsonMissingPath('data.review_rounds');
        $response->assertJsonMissingPath('data.editorial_events');
        $response->assertJsonMissingPath('data.revisions.0.review_rounds');
        $response->assertJsonMissingPath('data.revisions.0.assignments');
    }
}
