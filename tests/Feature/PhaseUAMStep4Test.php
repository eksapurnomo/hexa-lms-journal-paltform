<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Journal;
use App\Models\JournalMembership;
use App\Models\AcademicProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Spatie\Permission\Models\Role;

class PhaseUAMStep4Test extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        \Illuminate\Support\Facades\Event::fake();
        if (!Role::where('name', 'admin')->exists()) {
            Role::create(['name' => 'admin']);
        }
    }

    private function createUser()
    {
        return User::factory()->create();
    }

    private function createAdmin()
    {
        $admin = User::factory()->create(['is_admin' => true, 'is_root' => true]);
        $admin->assignRole('admin');
        return $admin;
    }

    private function createJournalOwner($journal)
    {
        $owner = User::factory()->create();
        JournalMembership::create([
            'user_id' => $owner->id,
            'journal_id' => $journal->id,
            'role' => 'owner',
            'status' => 'active'
        ]);
        return $owner;
    }

    public function test_platform_admin_can_access_user_management()
    {
        $admin = $this->createAdmin();
        $response = $this->actingAs($admin)->get('/admin/user');
        $response->assertStatus(200);
    }

    public function test_normal_user_cannot_access_user_management()
    {
        $user = $this->createUser();
        $response = $this->actingAs($user)->get('/admin/user');
        $this->assertNotEquals(200, $response->status());
    }

    public function test_journal_owner_cannot_access_platform_user_management()
    {
        $journal = Journal::create([
            'title' => 'Test Journal',
            'slug' => 'test-journal',
            'description' => 'Test',
            'status' => 'active'
        ]);
        $owner = $this->createJournalOwner($journal);

        $response = $this->actingAs($owner)->get('/admin/user');
        $this->assertNotEquals(200, $response->status());
    }

    public function test_admin_cannot_change_is_root_unless_root()
    {
        // Testing privilege escalation
        $admin = User::factory()->create(['is_admin' => true, 'is_root' => false]);
        $admin->assignRole('admin');

        $targetUser = $this->createUser();

        $response = $this->actingAs($admin)->put(route('user.update', $targetUser->id), [
            'name' => 'Hacked',
            'email' => $targetUser->email,
            'phone' => '1234567890',
            'is_admin' => 'on'
        ]);

        $response->assertStatus(302); // Redirect back on success
        
        $targetUser->refresh();
        // Since $admin is NOT root, the is_admin flag should NOT be updated to true
        $this->assertEmpty($targetUser->is_admin);
    }
}
