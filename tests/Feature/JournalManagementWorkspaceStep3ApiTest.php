<?php

namespace Tests\Feature;

use App\Models\Journal;
use App\Models\JournalMembership;
use App\Models\User;
use App\Models\Submission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JournalManagementWorkspaceStep3ApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_access_managed_journal_submissions()
    {
        $user = User::factory()->create(['is_admin' => false]);
        $journal = Journal::create(['title' => 'Test', 'slug' => 'test', 'status' => 'active', 'created_by' => $user->id]);
        JournalMembership::create(['user_id' => $user->id, 'journal_id' => $journal->id, 'role' => 'owner', 'status' => 'active']);

        $submission = Submission::forceCreate(['journal_id' => $journal->id, 'status' => 'submitted', 'created_by' => $user->id, 'title' => 'Sub1']);

        $response = $this->withoutExceptionHandling()->actingAs($user, 'api')->getJson("/api/user/journals/{$journal->slug}/management/submissions");
        
        $response->assertStatus(200);
        $response->assertJsonPath('data.0.id', $submission->id);
    }

    public function test_editor_can_only_access_assigned_submissions()
    {
        $editor1 = User::factory()->create();
        $editor2 = User::factory()->create();
        $journal = Journal::create(['title' => 'Test', 'slug' => 'test', 'status' => 'active', 'created_by' => $editor1->id]);
        
        JournalMembership::create(['user_id' => $editor1->id, 'journal_id' => $journal->id, 'role' => 'editor', 'status' => 'active']);
        
        $assigned = Submission::forceCreate(['journal_id' => $journal->id, 'editor_id' => $editor1->id, 'status' => 'submitted', 'created_by' => $editor1->id, 'title' => 'Assigned']);
        $unassigned = Submission::forceCreate(['journal_id' => $journal->id, 'editor_id' => $editor2->id, 'status' => 'submitted', 'created_by' => $editor1->id, 'title' => 'Unassigned']);

        $response = $this->actingAs($editor1, 'api')->getJson("/api/user/journals/{$journal->slug}/management/submissions");
        
        $response->assertStatus(200);
        // Should only see the assigned submission
        $this->assertCount(1, $response->json('data'));
        $this->assertEquals($assigned->id, $response->json('data.0.id'));
    }

    public function test_reviewer_cannot_access_journal_management_submissions()
    {
        $user = User::factory()->create();
        $journal = Journal::create(['title' => 'Test', 'slug' => 'test', 'status' => 'active', 'created_by' => $user->id]);
        JournalMembership::create(['user_id' => $user->id, 'journal_id' => $journal->id, 'role' => 'reviewer', 'status' => 'active']);

        $response = $this->actingAs($user, 'api')->getJson("/api/user/journals/{$journal->slug}/management/submissions");
        $response->assertStatus(403);
    }

    public function test_editor_of_journal_a_cannot_access_journal_b()
    {
        $user = User::factory()->create();
        $journalA = Journal::create(['title' => 'Test A', 'slug' => 'test-a', 'status' => 'active', 'created_by' => $user->id]);
        $journalB = Journal::create(['title' => 'Test B', 'slug' => 'test-b', 'status' => 'active', 'created_by' => $user->id]);
        JournalMembership::create(['user_id' => $user->id, 'journal_id' => $journalA->id, 'role' => 'editor', 'status' => 'active']);

        $response = $this->actingAs($user, 'api')->getJson("/api/user/journals/{$journalB->slug}/management/submissions");
        $response->assertStatus(403);
    }

    public function test_editor_cannot_access_unauthorized_submission_detail()
    {
        $editor = User::factory()->create();
        $otherEditor = User::factory()->create();
        $journal = Journal::create(['title' => 'Test', 'slug' => 'test', 'status' => 'active', 'created_by' => $editor->id]);
        JournalMembership::create(['user_id' => $editor->id, 'journal_id' => $journal->id, 'role' => 'editor', 'status' => 'active']);

        $submission = Submission::forceCreate(['journal_id' => $journal->id, 'editor_id' => $otherEditor->id, 'status' => 'submitted', 'created_by' => $editor->id, 'title' => 'Sub1']);

        $response = $this->actingAs($editor, 'api')->getJson("/api/user/journals/{$journal->slug}/management/submissions/{$submission->id}");
        $response->assertStatus(404);
    }

    public function test_cross_journal_submission_id_is_rejected()
    {
        $owner = User::factory()->create();
        $journalA = Journal::create(['title' => 'Test A', 'slug' => 'test-a', 'status' => 'active', 'created_by' => $owner->id]);
        $journalB = Journal::create(['title' => 'Test B', 'slug' => 'test-b', 'status' => 'active', 'created_by' => $owner->id]);
        
        JournalMembership::create(['user_id' => $owner->id, 'journal_id' => $journalA->id, 'role' => 'owner', 'status' => 'active']);
        
        $submissionB = Submission::forceCreate(['journal_id' => $journalB->id, 'status' => 'submitted', 'created_by' => $owner->id, 'title' => 'Sub B']);

        // Attempting to access submission B through journal A
        $response = $this->actingAs($owner, 'api')->getJson("/api/user/journals/{$journalA->slug}/management/submissions/{$submissionB->id}");
        $response->assertStatus(404);
    }
    
    public function test_process_page_respects_journal_authorization()
    {
        $user = User::factory()->create();
        $journal = Journal::create(['title' => 'Test', 'slug' => 'test', 'status' => 'active', 'created_by' => $user->id]);
        
        $response = $this->actingAs($user, 'api')->getJson("/api/user/journals/{$journal->slug}/management/editorial-process");
        $response->assertStatus(403);
        
        JournalMembership::create(['user_id' => $user->id, 'journal_id' => $journal->id, 'role' => 'editor', 'status' => 'active']);
        $response2 = $this->actingAs($user, 'api')->getJson("/api/user/journals/{$journal->slug}/management/editorial-process");
        $response2->assertStatus(200);
        
        $this->assertEquals('editor', $response2->json('data.role'));
    }
}
