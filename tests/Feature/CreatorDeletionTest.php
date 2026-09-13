<?php

namespace Tests\Feature;

use App\Models\Journal;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CreatorDeletionTest extends TestCase
{
    use RefreshDatabase;

    public function test_creator_deletion_does_not_delete_journal()
    {
        Role::firstOrCreate(['name' => 'admin']);
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $this->actingAs($admin)->postJson('/admin/journals', [
            'title' => 'Test Journal',
            'slug' => 'test-journal',
        ]);

        $journal = Journal::where('slug', 'test-journal')->first();
        $this->assertEquals($admin->id, $journal->created_by);

        // Delete the admin
        $admin->delete(); // This performs soft delete because User model uses SoftDeletes
        $admin->forceDelete(); // Ensure actual database row deletion for FK constraint test

        // Reload the journal
        $journal->refresh();

        $this->assertNull($journal->created_by);
        $this->assertEquals('Test Journal', $journal->title);
    }
}
