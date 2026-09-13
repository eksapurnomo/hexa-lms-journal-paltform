<?php

namespace Tests\Feature;

use App\Models\Journal;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JournalPublicTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Since we are overriding the DB entirely, we should just test functionality.
        // Or if we don't want to use RefreshDatabase on the entire DB, we can use DatabaseTransactions.
        // Wait, JournalFoundationTest uses RefreshDatabase, so we will use it too.
    }

    public function test_public_can_view_active_journals_directory()
    {
        Journal::create(['title' => 'Active Journal', 'slug' => 'active-journal', 'status' => 'active']);
        Journal::create(['title' => 'Draft Journal', 'slug' => 'draft-journal', 'status' => 'draft']);
        Journal::create(['title' => 'Archived Journal', 'slug' => 'archived-journal', 'status' => 'archived']);

        $response = $this->getJson('/api/journals');

        $response->assertStatus(200);
        $response->assertJsonFragment(['title' => 'Active Journal']);
        $response->assertJsonMissing(['title' => 'Draft Journal']);
        $response->assertJsonMissing(['title' => 'Archived Journal']);
    }

    public function test_public_can_view_active_journal_detail()
    {
        $journal = Journal::create(['title' => 'Active Journal', 'slug' => 'active-journal', 'status' => 'active']);

        $response = $this->getJson('/api/journals/active-journal');

        $response->assertStatus(200);
        $response->assertJsonFragment(['title' => 'Active Journal']);
    }

    public function test_public_cannot_view_draft_or_archived_journal_detail()
    {
        Journal::create(['title' => 'Draft Journal', 'slug' => 'draft-journal', 'status' => 'draft']);
        Journal::create(['title' => 'Archived Journal', 'slug' => 'archived-journal', 'status' => 'archived']);

        $response = $this->getJson('/api/journals/draft-journal');
        $response->assertStatus(404);

        $response = $this->getJson('/api/journals/archived-journal');
        $response->assertStatus(404);
    }

    public function test_invalid_slug_returns_404()
    {
        $response = $this->getJson('/api/journals/non-existent-journal');
        $response->assertStatus(404);
    }

    public function test_public_journal_resource_hides_sensitive_data()
    {
        $user = User::factory()->create();
        $journal = Journal::create([
            'title' => 'Secure Journal',
            'slug' => 'secure-journal',
            'status' => 'active',
            'created_by' => $user->id
        ]);

        $response = $this->getJson('/api/journals/secure-journal');
        
        $response->assertStatus(200);
        
        // Assert public fields exist
        $response->assertJsonFragment([
            'title' => 'Secure Journal',
            'slug' => 'secure-journal'
        ]);

        // Assert sensitive fields are hidden
        $json = $response->json();
        $this->assertArrayNotHasKey('id', $json['data']['journal']);
        $this->assertArrayNotHasKey('created_by', $json['data']['journal']);
        $this->assertArrayNotHasKey('status', $json['data']['journal']);
        $this->assertArrayNotHasKey('memberships', $json['data']['journal']);
    }

    public function test_membership_isolation_in_public_directory()
    {
        $user = User::factory()->create();
        
        // Journal A (Active, User is member)
        $journalA = Journal::create(['title' => 'Journal A', 'slug' => 'journal-a', 'status' => 'active']);
        $journalA->members()->attach($user->id, ['role' => 'editor']);

        // Journal B (Draft, User is member)
        $journalB = Journal::create(['title' => 'Journal B', 'slug' => 'journal-b', 'status' => 'draft']);
        $journalB->members()->attach($user->id, ['role' => 'owner']);

        // Authenticated request
        $response = $this->actingAs($user)->getJson('/api/journals');
        
        $response->assertStatus(200);
        $response->assertJsonFragment(['title' => 'Journal A']);
        // Draft journal should NOT appear in public directory even if user is owner
        $response->assertJsonMissing(['title' => 'Journal B']);
    }
}
