<?php

namespace Tests\Feature;

use App\Models\Journal;
use App\Models\JournalMembership;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class JournalFoundationTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        // create global admin role
        Role::firstOrCreate(['name' => 'admin']);
    }

    private function getAdminUser()
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        return $admin;
    }

    // A. Journal Creation Tests
    public function test_admin_can_create_journal()
    {
        $admin = $this->getAdminUser();

        $response = $this->actingAs($admin)->postJson('/admin/journals', [
            'title' => 'Test Journal',
            'slug' => 'test-journal',
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('journals', [
            'title' => 'Test Journal',
            'created_by' => $admin->id
        ]);
    }

    public function test_non_admin_cannot_create_journal()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/admin/journals', [
            'title' => 'Test Journal',
            'slug' => 'test-journal',
        ]);

        $response->assertStatus(403);
        $this->assertDatabaseMissing('journals', [
            'title' => 'Test Journal'
        ]);
    }

    public function test_unauthenticated_cannot_create_journal()
    {
        $response = $this->postJson('/admin/journals', [
            'title' => 'Test Journal',
            'slug' => 'test-journal',
        ]);

        $response->assertStatus(401);
    }

    // B. Journal Access Tests
    public function test_active_member_can_access_journal()
    {
        $user = User::factory()->create();
        $journal = Journal::create(['title' => 'A', 'slug' => 'a']);
        JournalMembership::create([
            'journal_id' => $journal->id,
            'user_id' => $user->id,
            'role' => 'editor',
            'status' => 'active'
        ]);

        $response = $this->actingAs($user)->getJson('/admin/journals');

        $response->assertStatus(200);
        $response->assertJsonFragment(['title' => 'A']);
    }

    public function test_non_member_cannot_access_journal_or_see_it()
    {
        $user = User::factory()->create();
        $journal = Journal::create(['title' => 'A', 'slug' => 'a']);

        $response = $this->actingAs($user)->getJson('/admin/journals');

        $response->assertStatus(200);
        $response->assertJsonMissing(['title' => 'A']);
    }

    public function test_inactive_member_cannot_see_journal()
    {
        $user = User::factory()->create();
        $journal = Journal::create(['title' => 'A', 'slug' => 'a']);
        JournalMembership::create([
            'journal_id' => $journal->id,
            'user_id' => $user->id,
            'role' => 'editor',
            'status' => 'inactive'
        ]);

        $response = $this->actingAs($user)->getJson('/admin/journals');

        $response->assertStatus(200);
        $response->assertJsonMissing(['title' => 'A']);
    }

    // C. Journal Update Tests
    public function test_owner_can_update_their_journal()
    {
        $user = User::factory()->create();
        $journal = Journal::create(['title' => 'A', 'slug' => 'a']);
        JournalMembership::create(['journal_id' => $journal->id, 'user_id' => $user->id, 'role' => 'owner', 'status' => 'active']);

        $response = $this->actingAs($user)->putJson("/admin/journals/{$journal->id}", [
            'title' => 'Updated A',
            'slug' => 'updated-a'
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('journals', ['title' => 'Updated A']);
    }

    public function test_editor_can_update_their_journal()
    {
        $user = User::factory()->create();
        $journal = Journal::create(['title' => 'A', 'slug' => 'a']);
        JournalMembership::create(['journal_id' => $journal->id, 'user_id' => $user->id, 'role' => 'editor', 'status' => 'active']);

        $response = $this->actingAs($user)->putJson("/admin/journals/{$journal->id}", [
            'title' => 'Updated A',
            'slug' => 'updated-a'
        ]);

        $response->assertStatus(200);
    }

    public function test_reviewer_cannot_update_journal()
    {
        $user = User::factory()->create();
        $journal = Journal::create(['title' => 'A', 'slug' => 'a']);
        JournalMembership::create(['journal_id' => $journal->id, 'user_id' => $user->id, 'role' => 'reviewer', 'status' => 'active']);

        $response = $this->actingAs($user)->putJson("/admin/journals/{$journal->id}", [
            'title' => 'Updated A',
            'slug' => 'updated-a'
        ]);

        $response->assertStatus(403);
        $this->assertDatabaseHas('journals', ['title' => 'A']);
    }

    public function test_editor_of_journal_A_cannot_update_journal_B()
    {
        $user = User::factory()->create();
        $journalA = Journal::create(['title' => 'A', 'slug' => 'a']);
        $journalB = Journal::create(['title' => 'B', 'slug' => 'b']);
        JournalMembership::create(['journal_id' => $journalA->id, 'user_id' => $user->id, 'role' => 'editor', 'status' => 'active']);

        $response = $this->actingAs($user)->putJson("/admin/journals/{$journalB->id}", [
            'title' => 'Updated B',
            'slug' => 'updated-b'
        ]);

        $response->assertStatus(403);
        $this->assertDatabaseHas('journals', ['title' => 'B']);
    }

    // D. Delete Tests
    public function test_owner_can_delete_journal()
    {
        $user = User::factory()->create();
        $journal = Journal::create(['title' => 'A', 'slug' => 'a']);
        JournalMembership::create(['journal_id' => $journal->id, 'user_id' => $user->id, 'role' => 'owner', 'status' => 'active']);

        $response = $this->actingAs($user)->deleteJson("/admin/journals/{$journal->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('journals', ['title' => 'A']);
    }

    public function test_editor_cannot_delete_journal()
    {
        $user = User::factory()->create();
        $journal = Journal::create(['title' => 'A', 'slug' => 'a']);
        JournalMembership::create(['journal_id' => $journal->id, 'user_id' => $user->id, 'role' => 'editor', 'status' => 'active']);

        $response = $this->actingAs($user)->deleteJson("/admin/journals/{$journal->id}");

        $response->assertStatus(403);
        $this->assertDatabaseHas('journals', ['title' => 'A']);
    }

    // E. Spoofing Tests
    public function test_created_by_spoofing()
    {
        $admin = $this->getAdminUser();

        $response = $this->actingAs($admin)->postJson('/admin/journals', [
            'title' => 'Spoof Test',
            'slug' => 'spoof-test',
            'created_by' => 999
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('journals', [
            'title' => 'Spoof Test',
            'created_by' => $admin->id
        ]);
        $this->assertDatabaseMissing('journals', [
            'title' => 'Spoof Test',
            'created_by' => 999
        ]);
    }

    public function test_cannot_spoof_membership()
    {
        $admin = $this->getAdminUser();

        $response = $this->actingAs($admin)->postJson('/admin/journals', [
            'title' => 'Membership Spoof',
            'slug' => 'membership-spoof',
            'role' => 'owner',
            'user_id' => 999,
            'journal_id' => 999,
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseMissing('journal_memberships', [
            'role' => 'owner',
            'user_id' => 999
        ]);
    }
}
