<?php

namespace Tests\Feature;

use App\Models\Journal;
use App\Models\JournalMembership;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class JournalManagementWorkspaceApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_authorized_user_can_access_workspace_api()
    {
        $user = User::factory()->create(['is_admin' => false]);
        $journal = Journal::create(['title' => 'Test', 'slug' => 'test', 'status' => 'active', 'created_by' => $user->id]);
        
        JournalMembership::create([
            'user_id' => $user->id,
            'journal_id' => $journal->id,
            'role' => 'editor',
            'status' => 'active',
        ]);

        $response = $this->actingAs($user, 'api')->getJson("/api/user/journals/{$journal->slug}/management/overview");

        $response->assertStatus(200);
        $response->assertJson([
            'message' => 'Journal management context retrieved successfully.',
            'data' => [
                'journal' => [
                    'id' => $journal->id,
                    'slug' => $journal->slug,
                    'title' => $journal->title,
                ],
                'role' => 'editor',
            ]
        ]);
        
        // Ensure snapshot and my work exist
        $this->assertArrayHasKey('editorial_snapshot', $response->json('data'));
        $this->assertArrayHasKey('my_editorial_work', $response->json('data'));
    }

    public function test_authenticated_user_without_permission_cannot_access_workspace()
    {
        $user = User::factory()->create();
        $journal = Journal::create(['title' => 'Test', 'slug' => 'test', 'status' => 'active', 'created_by' => $user->id]);
        
        // Unrelated user
        $response = $this->actingAs($user, 'api')->getJson("/api/user/journals/{$journal->slug}/management/overview");
        $response->assertStatus(403);
    }

    public function test_reviewer_without_management_permission_cannot_access_workspace()
    {
        $user = User::factory()->create();
        $journal = Journal::create(['title' => 'Test', 'slug' => 'test', 'status' => 'active', 'created_by' => $user->id]);
        
        JournalMembership::create([
            'user_id' => $user->id,
            'journal_id' => $journal->id,
            'role' => 'reviewer',
            'status' => 'active',
        ]);

        $response = $this->actingAs($user, 'api')->getJson("/api/user/journals/{$journal->slug}/management/overview");
        $response->assertStatus(403);
    }

    public function test_multi_journal_user_can_access_authorized_independently()
    {
        $user = User::factory()->create();
        $journalA = Journal::create(['title' => 'Test A', 'slug' => 'test-a', 'status' => 'active', 'created_by' => $user->id]);
        $journalB = Journal::create(['title' => 'Test B', 'slug' => 'test-b', 'status' => 'active', 'created_by' => $user->id]);
        $journalC = Journal::create(['title' => 'Test C', 'slug' => 'test-c', 'status' => 'active', 'created_by' => $user->id]);
        
        JournalMembership::create([
            'user_id' => $user->id,
            'journal_id' => $journalA->id,
            'role' => 'owner',
            'status' => 'active',
        ]);

        JournalMembership::create([
            'user_id' => $user->id,
            'journal_id' => $journalB->id,
            'role' => 'editor',
            'status' => 'active',
        ]);

        // Access Journal A
        $responseA = $this->actingAs($user, 'api')->getJson("/api/user/journals/{$journalA->slug}/management/overview");
        $responseA->assertStatus(200);
        $this->assertEquals('owner', $responseA->json('data.role'));

        // Access Journal B
        $responseB = $this->actingAs($user, 'api')->getJson("/api/user/journals/{$journalB->slug}/management/overview");
        $responseB->assertStatus(200);
        $this->assertEquals('editor', $responseB->json('data.role'));

        // Access Journal C (unauthorized)
        $responseC = $this->actingAs($user, 'api')->getJson("/api/user/journals/{$journalC->slug}/management/overview");
        $responseC->assertStatus(403);
    }

    public function test_no_arbitrary_user_id_can_override_authenticated_user()
    {
        $hacker = User::factory()->create();
        $owner = User::factory()->create();
        $journal = Journal::create(['title' => 'Test', 'slug' => 'test', 'status' => 'active', 'created_by' => $owner->id]);
        
        JournalMembership::create([
            'user_id' => $owner->id,
            'journal_id' => $journal->id,
            'role' => 'owner',
            'status' => 'active',
        ]);

        // Hacker tries to pass owner's ID
        $response = $this->actingAs($hacker, 'api')->getJson("/api/user/journals/{$journal->slug}/management/overview?user_id={$owner->id}");
        $response->assertStatus(403);
    }

    public function test_snapshot_counts_are_journal_scoped_and_workload_is_user_scoped()
    {
        $editor1 = User::factory()->create();
        $editor2 = User::factory()->create();
        
        $journalA = Journal::create(['title' => 'Journal A', 'slug' => 'journal-a', 'status' => 'active', 'created_by' => $editor1->id]);
        $journalB = Journal::create(['title' => 'Journal B', 'slug' => 'journal-b', 'status' => 'active', 'created_by' => $editor2->id]);
        
        JournalMembership::create([
            'user_id' => $editor1->id,
            'journal_id' => $journalA->id,
            'role' => 'editor',
            'status' => 'active',
        ]);

        // Create submissions for Journal A
        // Assigned to editor 1
        \App\Models\Submission::forceCreate(['journal_id' => $journalA->id, 'editor_id' => $editor1->id, 'status' => 'submitted', 'created_by' => $editor1->id, 'title' => 'Sub1', 'abstract' => 'abs']);
        \App\Models\Submission::forceCreate(['journal_id' => $journalA->id, 'editor_id' => $editor1->id, 'status' => 'editorial_assessment', 'created_by' => $editor1->id, 'title' => 'Sub2', 'abstract' => 'abs']);
        // Assigned to editor 2 (should count in snapshot, but NOT in editor 1's workload)
        \App\Models\Submission::forceCreate(['journal_id' => $journalA->id, 'editor_id' => $editor2->id, 'status' => 'review_pending', 'created_by' => $editor1->id, 'title' => 'Sub3', 'abstract' => 'abs']);
        
        // Create submission for Journal B (should NOT count in Journal A snapshot)
        \App\Models\Submission::forceCreate(['journal_id' => $journalB->id, 'editor_id' => $editor1->id, 'status' => 'submitted', 'created_by' => $editor1->id, 'title' => 'Sub4', 'abstract' => 'abs']);

        $response = $this->actingAs($editor1, 'api')->getJson("/api/user/journals/{$journalA->slug}/management/overview");
        $response->assertStatus(200);
        
        // Assert Snapshot (Journal level)
        $this->assertEquals(1, $response->json('data.editorial_snapshot.new_submissions'));
        $this->assertEquals(1, $response->json('data.editorial_snapshot.under_review'));
        
        // Assert Workload (User level - Editor 1)
        // Editor 1 is assigned 2 submissions in Journal A (Sub1, Sub2)
        $this->assertEquals(2, $response->json('data.my_editorial_work.assigned_submissions'));
        // Editor 1 has 1 pending action (Sub2 is editorial_assessment)
        $this->assertEquals(1, $response->json('data.my_editorial_work.pending_actions'));
    }
}
