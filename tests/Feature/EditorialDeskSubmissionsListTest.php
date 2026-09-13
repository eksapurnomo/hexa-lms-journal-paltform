<?php

namespace Tests\Feature;

use App\Models\Journal;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class EditorialDeskSubmissionsListTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Ensure admin role exists
        Role::firstOrCreate(['name' => 'admin']);
    }

    private function forceCreateSubmission(array $attributes)
    {
        $submission = new Submission();
        $submission->forceFill($attributes);
        $submission->save();
        return $submission;
    }

    public function test_admin_can_access_all_submissions()
    {
        $admin = User::factory()->create(['is_admin' => 1]);
        $admin->assignRole('admin');

        $journal = Journal::create(['title' => 'Test', 'slug' => 't1', 'created_by' => $admin->id]);
        
        $this->forceCreateSubmission([
            'journal_id' => $journal->id,
            'title' => 'Test Sub',
            'status' => 'submitted',
            'created_by' => $admin->id,
        ]);

        $response = $this->actingAs($admin)->getJson('/api/editorial/submissions');
        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data');
    }

    public function test_journal_owner_can_access_journal_submissions()
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();

        $journal = Journal::create(['title' => 'Test', 'slug' => 't1', 'created_by' => $owner->id]);
        $journal->memberships()->create(['user_id' => $owner->id, 'role' => 'owner', 'status' => 'active']);
        
        $this->forceCreateSubmission([
            'journal_id' => $journal->id,
            'title' => 'Test Sub',
            'status' => 'submitted',
            'created_by' => $otherUser->id,
        ]);

        $response = $this->actingAs($owner)->getJson('/api/editorial/submissions');
        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data');
    }

    public function test_assigned_editor_can_access_only_assigned_submissions()
    {
        $editor = User::factory()->create();
        $author = User::factory()->create();

        $journal = Journal::create(['title' => 'Test', 'slug' => 't1', 'created_by' => $editor->id]);
        $journal->memberships()->create(['user_id' => $editor->id, 'role' => 'editor', 'status' => 'active']);
        
        $assignedSub = $this->forceCreateSubmission([
            'journal_id' => $journal->id,
            'title' => 'Assigned Sub',
            'status' => 'submitted',
            'created_by' => $author->id,
            'editor_id' => $editor->id,
        ]);

        $unassignedSub = $this->forceCreateSubmission([
            'journal_id' => $journal->id,
            'title' => 'Unassigned Sub',
            'status' => 'submitted',
            'created_by' => $author->id,
        ]);

        $response = $this->actingAs($editor)->getJson('/api/editorial/submissions');
        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data');
        $this->assertEquals('Assigned Sub', $response->json('data.0.title'));
    }

    public function test_assigned_editor_sees_only_assigned_when_not_author()
    {
        $editor = User::factory()->create();
        $author = User::factory()->create();
        
        $journal = Journal::create(['title' => 'Test', 'slug' => 't1', 'created_by' => $editor->id]);
        $journal->memberships()->create(['user_id' => $editor->id, 'role' => 'editor', 'status' => 'active']);
        
        $this->forceCreateSubmission([
            'journal_id' => $journal->id,
            'title' => 'Assigned Sub',
            'status' => 'submitted',
            'created_by' => $author->id,
            'editor_id' => $editor->id,
        ]);

        $this->forceCreateSubmission([
            'journal_id' => $journal->id,
            'title' => 'Unassigned Sub',
            'status' => 'submitted',
            'created_by' => $author->id,
        ]);

        $response = $this->actingAs($editor)->getJson('/api/editorial/submissions');
        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data');
        $this->assertEquals('Assigned Sub', $response->json('data.0.title'));
    }

    public function test_search_filters_and_pagination()
    {
        $admin = User::factory()->create(['is_admin' => 1]);
        $admin->assignRole('admin');

        $journal1 = Journal::create(['title' => 'J1', 'slug' => 'j1', 'created_by' => $admin->id]);
        $journal2 = Journal::create(['title' => 'J2', 'slug' => 'j2', 'created_by' => $admin->id]);
        
        $this->forceCreateSubmission([
            'journal_id' => $journal1->id,
            'title' => 'Alpha paper',
            'status' => 'submitted',
            'created_by' => $admin->id,
        ]);

        $this->forceCreateSubmission([
            'journal_id' => $journal1->id,
            'title' => 'Beta paper',
            'status' => 'revision_required',
            'created_by' => $admin->id,
        ]);

        $this->forceCreateSubmission([
            'journal_id' => $journal2->id,
            'title' => 'Gamma paper',
            'status' => 'submitted',
            'created_by' => $admin->id,
        ]);

        // Test search
        $response = $this->actingAs($admin)->getJson('/api/editorial/submissions?search=Alpha');
        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data');
        
        // Test journal filter
        $response = $this->actingAs($admin)->getJson('/api/editorial/submissions?journal_id=' . $journal2->id);
        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data');

        // Test status filter
        $response = $this->actingAs($admin)->getJson('/api/editorial/submissions?status=revision_required');
        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data');

        // Test search bypassing authorization
        // Unassigned editor searches for Alpha paper
        $editor = User::factory()->create();
        $journal1->memberships()->create(['user_id' => $editor->id, 'role' => 'editor', 'status' => 'active']);
        
        $response = $this->actingAs($editor)->getJson('/api/editorial/submissions?search=Alpha');
        $response->assertStatus(200);
        $response->assertJsonCount(0, 'data'); // Should be 0 since not assigned
    }
}
