<?php

namespace Tests\Feature;

use App\Models\Journal;
use App\Models\JournalMembership;
use App\Models\Submission;
use App\Models\SubmissionAuthor;
use App\Models\SubmissionEditorialEvent;
use App\Models\SubmissionFile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class EditorialDeskSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        if (config('database.default') !== 'mysql' || config('database.connections.mysql.database') !== 'readylms_testing') {
            $this->markTestSkipped('Not using testing database.');
        }
    }

    private function createAdmin()
    {
        return User::factory()->create(['is_admin' => true]);
    }

    private function createJournalOwner($journal)
    {
        $user = User::factory()->create();
        JournalMembership::create([
            'journal_id' => $journal->id,
            'user_id' => $user->id,
            'role' => 'owner',
            'status' => 'active',
        ]);
        return $user;
    }

    private function createJournalEditor($journal)
    {
        $user = User::factory()->create();
        JournalMembership::create([
            'journal_id' => $journal->id,
            'user_id' => $user->id,
            'role' => 'editor',
            'status' => 'active',
        ]);
        return $user;
    }

    private function createReviewer($journal)
    {
        $user = User::factory()->create();
        JournalMembership::create([
            'journal_id' => $journal->id,
            'user_id' => $user->id,
            'role' => 'reviewer',
            'status' => 'active',
        ]);
        return $user;
    }

    private function createSubmission($journal, $author, $status = 'draft', $editorId = null)
    {
        $sub = Submission::forceCreate([
            'journal_id' => $journal->id,
            'created_by' => $author->id,
            'title' => 'Test',
            'abstract' => 'Test',
            'status' => $status,
            'editor_id' => $editorId,
        ]);
        
        SubmissionAuthor::create([
            'submission_id' => $sub->id,
            'user_id' => $author->id,
            'first_name' => 'John',
            'is_corresponding' => true,
            'sequence' => 1,
        ]);
        
        $revision = \App\Models\SubmissionRevision::create([
            'submission_id' => $sub->id,
            'version_number' => 1,
        ]);
        
        $revision->files()->create([
            'disk' => 'local',
            'file_path' => 'submissions/' . $sub->id . '/test.pdf',
            'original_name' => 'test.pdf',
            'mime_type' => 'application/pdf',
            'size' => 1024,
        ]);
        
        \App\Models\ReviewRound::create([
            'submission_revision_id' => $revision->id,
            'round_number' => 1,
        ]);
        
        return $sub;
    }

    public function test_unauthenticated_editorial_access_denied()
    {
        $journal = Journal::create(['title' => 'Test Journal', 'slug' => uniqid(), 'status' => 'active']);
        $author = User::factory()->create();
        $sub = $this->createSubmission($journal, $author, Submission::STATUS_SUBMITTED);
        
        $this->getJson('/api/editorial/submissions')->assertStatus(401);
        $this->getJson("/api/editorial/submissions/{$sub->id}")->assertStatus(401);
        $this->patchJson("/api/editorial/submissions/{$sub->id}/assign")->assertStatus(401);
        $this->postJson("/api/editorial/submissions/{$sub->id}/rounds/1/decision")->assertStatus(401);
    }
    
    public function test_admin_can_access_any_submission()
    {
        $admin = $this->createAdmin();
        $journal = Journal::create(['title' => 'Test Journal', 'slug' => uniqid(), 'status' => 'active']);
        $author = User::factory()->create();
        $sub = $this->createSubmission($journal, $author, Submission::STATUS_SUBMITTED);
        
        $this->actingAs($admin)->getJson("/api/editorial/submissions/{$sub->id}")->assertStatus(200);
        $this->actingAs($admin)->getJson('/api/editorial/submissions')->assertStatus(200)->assertJsonCount(1, 'data');
    }

    public function test_owner_can_access_own_journal_submission()
    {
        $journal = Journal::create(['title' => 'Test Journal', 'slug' => uniqid(), 'status' => 'active']);
        $owner = $this->createJournalOwner($journal);
        $author = User::factory()->create();
        $sub = $this->createSubmission($journal, $author, Submission::STATUS_SUBMITTED);
        
        $this->actingAs($owner)->getJson("/api/editorial/submissions/{$sub->id}")->assertStatus(200);
    }
    
    public function test_owner_cannot_access_other_journal_submission()
    {
        $journalA = Journal::create(['title' => 'Test', 'slug' => uniqid(), 'status' => 'active']);
        $ownerA = $this->createJournalOwner($journalA);
        
        $journalB = Journal::create(['title' => 'Test', 'slug' => uniqid(), 'status' => 'active']);
        $author = User::factory()->create();
        $subB = $this->createSubmission($journalB, $author, Submission::STATUS_SUBMITTED);
        
        $this->actingAs($ownerA)->getJson("/api/editorial/submissions/{$subB->id}")->assertStatus(403);
    }
    
    public function test_assigned_editor_can_access_assigned_submission()
    {
        $journal = Journal::create(['title' => 'Test Journal', 'slug' => uniqid(), 'status' => 'active']);
        $editor = $this->createJournalEditor($journal);
        $author = User::factory()->create();
        $sub = $this->createSubmission($journal, $author, Submission::STATUS_SUBMITTED, $editor->id);
        
        $this->actingAs($editor)->getJson("/api/editorial/submissions/{$sub->id}")->assertStatus(200);
    }
    
    public function test_unassigned_editor_cannot_access_submission()
    {
        $journal = Journal::create(['title' => 'Test Journal', 'slug' => uniqid(), 'status' => 'active']);
        $editor = $this->createJournalEditor($journal);
        $author = User::factory()->create();
        $sub = $this->createSubmission($journal, $author, Submission::STATUS_SUBMITTED, null); // Unassigned
        
        $this->actingAs($editor)->getJson("/api/editorial/submissions/{$sub->id}")->assertStatus(403);
    }
    
    public function test_editor_b_cannot_access_editor_a_submission()
    {
        $journal = Journal::create(['title' => 'Test Journal', 'slug' => uniqid(), 'status' => 'active']);
        $editorA = $this->createJournalEditor($journal);
        $editorB = $this->createJournalEditor($journal);
        $author = User::factory()->create();
        
        $sub = $this->createSubmission($journal, $author, Submission::STATUS_SUBMITTED, $editorA->id);
        
        $this->actingAs($editorB)->getJson("/api/editorial/submissions/{$sub->id}")->assertStatus(403);
    }
    
    public function test_author_cannot_access_editorial_endpoints()
    {
        $journal = Journal::create(['title' => 'Test', 'slug' => uniqid(), 'status' => 'active']);
        $author = User::factory()->create();
        $sub = $this->createSubmission($journal, $author, Submission::STATUS_SUBMITTED);
        $round = \App\Models\ReviewRound::whereHas('submissionRevision', function($q) use ($sub) { $q->where('submission_id', $sub->id); })->first();
        
        $this->actingAs($author)->getJson("/api/editorial/submissions/{$sub->id}")->assertStatus(403);
        $this->actingAs($author)->postJson("/api/editorial/submissions/{$sub->id}/rounds/{$round->id}/decision")->assertStatus(403);
    }

    public function test_owner_can_assign_editor_and_creates_audit_log()
    {
        $journal = Journal::create(['title' => 'Test', 'slug' => uniqid(), 'status' => 'active']);
        $owner = $this->createJournalOwner($journal);
        $editor = $this->createJournalEditor($journal);
        $author = User::factory()->create();
        $sub = $this->createSubmission($journal, $author, Submission::STATUS_SUBMITTED);
        
        $response = $this->actingAs($owner)->patchJson("/api/editorial/submissions/{$sub->id}/assign", [
            'editor_id' => $editor->id,
        ]);
        
        $response->assertStatus(200);
        $this->assertDatabaseHas('submissions', ['id' => $sub->id, 'editor_id' => $editor->id]);
        $this->assertDatabaseHas('submission_editorial_events', [
            'submission_id' => $sub->id,
            'user_id' => $owner->id,
            'action' => SubmissionEditorialEvent::ACTION_ASSIGNED,
        ]);
    }
    
    public function test_editor_cannot_assign()
    {
        $journal = Journal::create(['title' => 'Test', 'slug' => uniqid(), 'status' => 'active']);
        $editor = $this->createJournalEditor($journal);
        $editorTarget = $this->createJournalEditor($journal);
        $author = User::factory()->create();
        // Even if assigned, they cannot reassign
        $sub = $this->createSubmission($journal, $author, Submission::STATUS_SUBMITTED, $editor->id);
        
        $response = $this->actingAs($editor)->patchJson("/api/editorial/submissions/{$sub->id}/assign", [
            'editor_id' => $editorTarget->id,
        ]);
        
        $response->assertStatus(403);
    }
    
    public function test_cannot_assign_editor_from_different_journal()
    {
        $journal = Journal::create(['title' => 'Test', 'slug' => uniqid(), 'status' => 'active']);
        $owner = $this->createJournalOwner($journal);
        
        $journalB = Journal::create(['title' => 'Test', 'slug' => uniqid(), 'status' => 'active']);
        $editorB = $this->createJournalEditor($journalB);
        
        $author = User::factory()->create();
        $sub = $this->createSubmission($journal, $author, Submission::STATUS_SUBMITTED);
        
        $response = $this->actingAs($owner)->patchJson("/api/editorial/submissions/{$sub->id}/assign", [
            'editor_id' => $editorB->id,
        ]);
        
        $response->assertStatus(422); // Validation fails in controller
    }
    
    public function test_cannot_assign_reviewer()
    {
        $journal = Journal::create(['title' => 'Test', 'slug' => uniqid(), 'status' => 'active']);
        $owner = $this->createJournalOwner($journal);
        $reviewer = $this->createReviewer($journal);
        
        $author = User::factory()->create();
        $sub = $this->createSubmission($journal, $author, Submission::STATUS_SUBMITTED);
        
        $response = $this->actingAs($owner)->patchJson("/api/editorial/submissions/{$sub->id}/assign", [
            'editor_id' => $reviewer->id,
        ]);
        
        $response->assertStatus(422);
    }
    
    public function test_valid_editorial_transition()
    {
        $journal = Journal::create(['title' => 'Test', 'slug' => uniqid(), 'status' => 'active']);
        $editor = $this->createJournalEditor($journal);
        $author = User::factory()->create();
        $sub = $this->createSubmission($journal, $author, Submission::STATUS_SUBMITTED, $editor->id);
        $round = \App\Models\ReviewRound::whereHas('submissionRevision', function($q) use ($sub) { $q->where('submission_id', $sub->id); })->first();
        
        $response = $this->actingAs($editor)->postJson("/api/editorial/submissions/{$sub->id}/rounds/{$round->id}/decision", [
            'decision' => 'revision_required',
        ]);
        
        $response->assertStatus(200);
        $this->assertEquals(Submission::STATUS_REVISION_REQUIRED, $sub->fresh()->status);
        $this->assertDatabaseHas('submission_editorial_events', [
            'submission_id' => $sub->id,
            'action' => 'editorial_decision_recorded',
        ]);
    }
    
    public function test_invalid_editorial_transition_fails()
    {
        $journal = Journal::create(['title' => 'Test', 'slug' => uniqid(), 'status' => 'active']);
        $editor = $this->createJournalEditor($journal);
        $author = User::factory()->create();
        $sub = $this->createSubmission($journal, $author, Submission::STATUS_SUBMITTED, $editor->id);
        $round = \App\Models\ReviewRound::whereHas('submissionRevision', function($q) use ($sub) { $q->where('submission_id', $sub->id); })->first();
        
        $response = $this->actingAs($editor)->postJson("/api/editorial/submissions/{$sub->id}/rounds/{$round->id}/decision", [
            'decision' => 'invalid_decision_type',
        ]);
        
        $response->assertStatus(422);
        $this->assertEquals(Submission::STATUS_SUBMITTED, $sub->fresh()->status);
        $this->assertDatabaseMissing('submission_editorial_events', [
            'submission_id' => $sub->id,
            'action' => 'editorial_decision_recorded',
        ]);
    }
    
    public function test_author_can_submit_revision()
    {
        $journal = Journal::create(['title' => 'Test', 'slug' => uniqid(), 'status' => 'active']);
        $author = User::factory()->create();
        $sub = $this->createSubmission($journal, $author, Submission::STATUS_REVISION_REQUIRED);
        
        $response = $this->actingAs($author, 'api')->postJson("/api/submissions/{$sub->id}/revision");
        
        $response->assertStatus(200);
        $this->assertEquals(Submission::STATUS_REVISION_SUBMITTED, $sub->fresh()->status);
        $this->assertDatabaseHas('submission_editorial_events', [
            'submission_id' => $sub->id,
            'action' => SubmissionEditorialEvent::ACTION_REVISION_SUBMITTED,
        ]);
    }

    public function test_author_cannot_submit_revision_if_not_required()
    {
        $journal = Journal::create(['title' => 'Test', 'slug' => uniqid(), 'status' => 'active']);
        $author = User::factory()->create();
        $sub = $this->createSubmission($journal, $author, Submission::STATUS_SUBMITTED);
        
        $response = $this->actingAs($author, 'api')->postJson("/api/submissions/{$sub->id}/revision");
        
        $response->assertStatus(403);
    }
    
    public function test_author_cannot_arbitrarily_change_status()
    {
        $journal = Journal::create(['title' => 'Test', 'slug' => uniqid(), 'status' => 'active']);
        $author = User::factory()->create();
        $sub = $this->createSubmission($journal, $author, Submission::STATUS_REVISION_REQUIRED);
        
        // Author uses standard update
        $response = $this->actingAs($author, 'api')->putJson("/api/submissions/{$sub->id}", [
            'status' => 'accepted' // Attempt spoof
        ]);
        
        $response->assertStatus(200); // Standard update succeeds
        $this->assertEquals(Submission::STATUS_REVISION_REQUIRED, $sub->fresh()->status); // But status remains unchanged
    }

    public function test_manuscript_download_isolation()
    {
        Storage::fake('local');
        $journal = Journal::create(['title' => 'Test Journal', 'slug' => uniqid(), 'status' => 'active']);
        $editorA = $this->createJournalEditor($journal);
        $editorB = $this->createJournalEditor($journal);
        $author = User::factory()->create();
        $sub = $this->createSubmission($journal, $author, Submission::STATUS_SUBMITTED, $editorA->id);
        $fileId = $sub->files->first()->id;

        // Create actual file in fake storage
        Storage::disk('local')->put($sub->files->first()->file_path, 'test');
        
        // Author can download
        $this->actingAs($author, 'api')->getJson("/api/submissions/{$sub->id}/files/{$fileId}/download")->assertStatus(200);
        
        // Assigned Editor A can download
        $this->actingAs($editorA, 'api')->getJson("/api/submissions/{$sub->id}/files/{$fileId}/download")->assertStatus(200);
        
        // Unassigned Editor B cannot
        $this->actingAs($editorB, 'api')->getJson("/api/submissions/{$sub->id}/files/{$fileId}/download")->assertStatus(403);
    }

    public function test_admin_can_retrieve_eligible_editors()
    {
        $admin = $this->createAdmin();
        $journal = Journal::create(['title' => 'Test', 'slug' => uniqid(), 'status' => 'active']);
        $author = User::factory()->create();
        $sub = $this->createSubmission($journal, $author, Submission::STATUS_SUBMITTED);
        
        $response = $this->actingAs($admin)->getJson("/api/editorial/submissions/{$sub->id}/eligible-editors");
        $response->assertStatus(200);
    }

    public function test_owner_can_retrieve_eligible_editors_for_own_journal()
    {
        $journal = Journal::create(['title' => 'Test', 'slug' => uniqid(), 'status' => 'active']);
        $owner = $this->createJournalOwner($journal);
        $author = User::factory()->create();
        $sub = $this->createSubmission($journal, $author, Submission::STATUS_SUBMITTED);
        
        $response = $this->actingAs($owner)->getJson("/api/editorial/submissions/{$sub->id}/eligible-editors");
        $response->assertStatus(200);
    }

    public function test_owner_cannot_retrieve_eligible_editors_for_other_journal()
    {
        $journalA = Journal::create(['title' => 'Test A', 'slug' => uniqid(), 'status' => 'active']);
        $ownerA = $this->createJournalOwner($journalA);
        
        $journalB = Journal::create(['title' => 'Test B', 'slug' => uniqid(), 'status' => 'active']);
        $author = User::factory()->create();
        $subB = $this->createSubmission($journalB, $author, Submission::STATUS_SUBMITTED);
        
        $response = $this->actingAs($ownerA)->getJson("/api/editorial/submissions/{$subB->id}/eligible-editors");
        $response->assertStatus(403);
    }

    public function test_editor_cannot_retrieve_eligible_editors()
    {
        $journal = Journal::create(['title' => 'Test', 'slug' => uniqid(), 'status' => 'active']);
        $editor = $this->createJournalEditor($journal);
        $author = User::factory()->create();
        $sub = $this->createSubmission($journal, $author, Submission::STATUS_SUBMITTED, $editor->id);
        
        $response = $this->actingAs($editor)->getJson("/api/editorial/submissions/{$sub->id}/eligible-editors");
        $response->assertStatus(403);
    }

    public function test_eligible_editors_response_format_and_filters()
    {
        $journal = Journal::create(['title' => 'Test', 'slug' => uniqid(), 'status' => 'active']);
        $owner = $this->createJournalOwner($journal);
        
        $activeEditor = $this->createJournalEditor($journal);
        $reviewer = $this->createReviewer($journal);
        
        $inactiveEditor = User::factory()->create();
        JournalMembership::create([
            'journal_id' => $journal->id,
            'user_id' => $inactiveEditor->id,
            'role' => 'editor',
            'status' => 'inactive',
        ]);
        
        $otherJournal = Journal::create(['title' => 'Test Other', 'slug' => uniqid(), 'status' => 'active']);
        $otherEditor = $this->createJournalEditor($otherJournal);

        $author = User::factory()->create();
        $sub = $this->createSubmission($journal, $author, Submission::STATUS_SUBMITTED);
        
        $response = $this->actingAs($owner)->getJson("/api/editorial/submissions/{$sub->id}/eligible-editors");
        
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'message',
            'data' => [
                '*' => ['id', 'name', 'email']
            ]
        ]);
        
        $data = collect($response->json('data'));
        
        $this->assertTrue($data->contains('id', $activeEditor->id));
        $this->assertFalse($data->contains('id', $reviewer->id));
        $this->assertFalse($data->contains('id', $inactiveEditor->id));
        $this->assertFalse($data->contains('id', $otherEditor->id));
        $this->assertArrayNotHasKey('password', $data->first());
    }
}
