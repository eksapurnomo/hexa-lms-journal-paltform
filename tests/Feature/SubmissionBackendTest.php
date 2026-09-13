<?php

namespace Tests\Feature;

use App\Models\Journal;
use App\Models\JournalMembership;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SubmissionBackendTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Since we are mocking storage, ensure the local disk is faked
        Storage::fake('local');
    }

    public function test_unauthenticated_cannot_access_submissions()
    {
        $response = $this->getJson('/api/submissions');
        $response->assertStatus(401);
    }

    public function test_author_can_create_draft_submission()
    {
        $author = User::factory()->create();
        $journal = Journal::create([
            'title' => 'Test Journal',
            'slug' => 'test-journal-' . uniqid(),
            'status' => 'active'
        ]);

        $payload = [
            'journal_id' => $journal->id,
            'title' => 'My First Manuscript',
            'abstract' => 'This is the abstract.',
            'authors' => [
                [
                    'first_name' => 'John',
                    'last_name' => 'Doe',
                    'is_corresponding' => true,
                ]
            ]
        ];

        $response = $this->actingAs($author, 'api')->postJson('/api/submissions', $payload);

        $response->assertStatus(201);
        
        $this->assertDatabaseHas('submissions', [
            'title' => 'My First Manuscript',
            'created_by' => $author->id,
            'status' => 'draft',
            'journal_id' => $journal->id,
        ]);

        $this->assertDatabaseHas('submission_authors', [
            'first_name' => 'John',
            'sequence' => 1,
            'is_corresponding' => 1,
        ]);
    }

    public function test_author_cannot_spoof_ownership()
    {
        $author = User::factory()->create();
        $otherUser = User::factory()->create();
        $journal = Journal::create([
            'title' => 'Test Journal',
            'slug' => 'test-journal-' . uniqid(),
            'status' => 'active'
        ]);

        $payload = [
            'journal_id' => $journal->id,
            'title' => 'Spoofed Manuscript',
            'created_by' => $otherUser->id, // Spoofing attempt
            'user_id' => $otherUser->id,
            'author_id' => $otherUser->id,
        ];

        $response = $this->actingAs($author, 'api')->postJson('/api/submissions', $payload);

        $response->assertStatus(201);
        
        // Ownership MUST be the authenticated user
        $this->assertDatabaseHas('submissions', [
            'title' => 'Spoofed Manuscript',
            'created_by' => $author->id, 
        ]);
    }

    public function test_cannot_submit_to_inactive_journal()
    {
        $author = User::factory()->create();
        $journal = Journal::create([
            'title' => 'Test Journal',
            'slug' => 'test-journal-' . uniqid(),
            'status' => 'draft'
        ]);

        $payload = [
            'journal_id' => $journal->id,
            'title' => 'Draft Journal Manuscript',
        ];

        $response = $this->actingAs($author, 'api')->postJson('/api/submissions', $payload);
        $response->assertStatus(422);
    }

    public function test_author_can_only_view_own_submissions()
    {
        $author = User::factory()->create();
        $otherAuthor = User::factory()->create();
        $journal = Journal::create([
            'title' => 'Test Journal',
            'slug' => 'test-journal-' . uniqid(),
            'status' => 'active'
        ]);

        $mySubmission = Submission::forceCreate([
            'journal_id' => $journal->id,
            'created_by' => $author->id,
            'title' => 'My Submission',
            'status' => 'draft',
        ]);

        $otherSubmission = Submission::forceCreate([
            'journal_id' => $journal->id,
            'created_by' => $otherAuthor->id,
            'title' => 'Other Submission',
            'status' => 'draft',
        ]);

        // List test
        $response = $this->actingAs($author, 'api')->getJson('/api/submissions');
        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data');
        $response->assertJsonPath('data.0.title', 'My Submission');

        // View single test
        $this->actingAs($author, 'api')->getJson("/api/submissions/{$mySubmission->id}")->assertStatus(200);
        $this->actingAs($author, 'api')->getJson("/api/submissions/{$otherSubmission->id}")->assertStatus(403);
    }

    public function test_editor_can_view_journal_submissions()
    {
        $author = User::factory()->create();
        $editor = User::factory()->create();
        
        $journal = Journal::create([
            'title' => 'Test Journal 1',
            'slug' => 'test-journal-1-' . uniqid(),
            'status' => 'active'
        ]);
        $otherJournal = Journal::create([
            'title' => 'Test Journal 2',
            'slug' => 'test-journal-2-' . uniqid(),
            'status' => 'active'
        ]);

        JournalMembership::create([
            'journal_id' => $journal->id,
            'user_id' => $editor->id,
            'role' => 'editor',
            'status' => 'active',
        ]);

        $submission = Submission::forceCreate([
            'journal_id' => $journal->id,
            'created_by' => $author->id,
            'editor_id' => $editor->id,
            'title' => 'My Submission',
            'status' => 'draft',
        ]);

        $otherSubmission = Submission::forceCreate([
            'journal_id' => $otherJournal->id,
            'created_by' => $author->id,
            'title' => 'Other Submission',
            'status' => 'draft',
        ]);

        // List test - should only see submissions from their journal
        $response = $this->actingAs($editor, 'api')->getJson('/api/submissions');
        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data');
        $response->assertJsonPath('data.0.title', 'My Submission');

        // Single view
        $this->actingAs($editor, 'api')->getJson("/api/submissions/{$submission->id}")->assertStatus(200);
        $this->actingAs($editor, 'api')->getJson("/api/submissions/{$otherSubmission->id}")->assertStatus(403);
    }

    public function test_author_can_upload_and_download_private_file()
    {
        $author = User::factory()->create();
        $journal = Journal::create([
            'title' => 'Test Journal',
            'slug' => 'test-journal-' . uniqid(),
            'status' => 'active'
        ]);

        $submission = Submission::forceCreate([
            'journal_id' => $journal->id,
            'created_by' => $author->id,
            'title' => 'My Submission',
            'status' => 'draft',
        ]);

        $file = UploadedFile::fake()->create('manuscript.pdf', 1000, 'application/pdf');

        $uploadResponse = $this->actingAs($author, 'api')->postJson("/api/submissions/{$submission->id}/files", [
            'file' => $file,
        ]);

        $uploadResponse->assertStatus(200);

        $submissionFile = $submission->files()->first();
        $this->assertNotNull($submissionFile);
        $this->assertEquals('local', $submissionFile->disk); // Must be private local disk
        
        Storage::disk('local')->assertExists($submissionFile->file_path);

        // Test download
        $downloadResponse = $this->actingAs($author, 'api')->get("/api/submissions/{$submission->id}/files/{$submissionFile->id}/download");
        $downloadResponse->assertStatus(200);

        // Ensure other users cannot download
        $otherUser = User::factory()->create();
        $this->actingAs($otherUser, 'api')->get("/api/submissions/{$submission->id}/files/{$submissionFile->id}/download")->assertStatus(403);
    }

    public function test_submission_transition_to_submitted()
    {
        $author = User::factory()->create();
        $journal = Journal::create([
            'title' => 'Test Journal',
            'slug' => 'test-journal-' . uniqid(),
            'status' => 'active'
        ]);

        $submission = Submission::forceCreate([
            'journal_id' => $journal->id,
            'created_by' => $author->id,
            'title' => 'My Submission',
            'status' => 'draft',
        ]);

        $submission->authors()->create([
            'first_name' => 'John',
            'sequence' => 1,
            'is_corresponding' => 1,
        ]);

        $file = UploadedFile::fake()->create('manuscript.pdf', 100, 'application/pdf');
        $this->actingAs($author, 'api')->postJson("/api/submissions/{$submission->id}/files", ['file' => $file]);

        $response = $this->actingAs($author, 'api')->postJson("/api/submissions/{$submission->id}/submit");
        $response->assertStatus(200);

        $this->assertDatabaseHas('submissions', [
            'id' => $submission->id,
            'status' => 'submitted',
        ]);
        
        $submission->refresh();
        $this->assertNotNull($submission->submitted_at);

        // Verify it becomes immutable
        $updateResponse = $this->actingAs($author, 'api')->putJson("/api/submissions/{$submission->id}", ['title' => 'New Title']);
        $updateResponse->assertStatus(403);

        $deleteResponse = $this->actingAs($author, 'api')->deleteJson("/api/submissions/{$submission->id}");
        $deleteResponse->assertStatus(403);

        $fileReplaceResponse = $this->actingAs($author, 'api')->postJson("/api/submissions/{$submission->id}/files", ['file' => clone $file]);
        $fileReplaceResponse->assertStatus(403);
    }
}
