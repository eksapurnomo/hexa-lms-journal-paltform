<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Journal;
use App\Models\JournalMembership;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Spatie\Permission\Models\Role;

class PhaseUAMUIFixTest extends TestCase
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

    private function createNormalUser()
    {
        return User::factory()->create();
    }

    private function createRootAdmin()
    {
        $admin = User::factory()->create(['is_admin' => true, 'is_root' => true]);
        $admin->assignRole('admin');
        return $admin;
    }

    private function createNonRootAdmin()
    {
        $admin = User::factory()->create(['is_admin' => true, 'is_root' => false]);
        $admin->assignRole('admin');
        return $admin;
    }

    private function createJournalMember($role)
    {
        $journal = Journal::create([
            'title' => 'Test Journal',
            'slug' => 'test-journal',
            'description' => 'Test',
            'status' => 'active'
        ]);

        $user = User::factory()->create();
        JournalMembership::create([
            'user_id' => $user->id,
            'journal_id' => $journal->id,
            'role' => $role,
            'status' => 'active'
        ]);

        return $user;
    }

    public function test_root_admin_can_access_academic_reviewers()
    {
        $admin = $this->createRootAdmin();
        $response = $this->actingAs($admin)->get('/admin/academic-reviewers');
        $response->assertStatus(200);
    }

    public function test_non_root_admin_can_access_academic_reviewers()
    {
        $admin = $this->createNonRootAdmin();
        $response = $this->actingAs($admin)->get('/admin/academic-reviewers');
        $response->assertStatus(200);
    }

    public function test_normal_user_is_denied()
    {
        $user = $this->createNormalUser();
        $response = $this->actingAs($user)->get('/admin/academic-reviewers');
        $this->assertNotEquals(200, $response->status());
    }

    public function test_journal_owner_is_denied()
    {
        $owner = $this->createJournalMember('owner');
        $response = $this->actingAs($owner)->get('/admin/academic-reviewers');
        $this->assertNotEquals(200, $response->status());
    }

    public function test_editor_is_denied()
    {
        $editor = $this->createJournalMember('editor');
        $response = $this->actingAs($editor)->get('/admin/academic-reviewers');
        $this->assertNotEquals(200, $response->status());
    }

    public function test_reviewer_is_denied()
    {
        $reviewer = $this->createJournalMember('reviewer');
        $response = $this->actingAs($reviewer)->get('/admin/academic-reviewers');
        $this->assertNotEquals(200, $response->status());
    }

    public function test_existing_student_management_still_works()
    {
        $admin = $this->createRootAdmin();
        $response = $this->actingAs($admin)->get('/admin/user');
        $response->assertStatus(200);
    }

    public function test_existing_editorial_desk_still_works()
    {
        $admin = $this->createRootAdmin();
        $response = $this->actingAs($admin)->get('/admin/editorial');
        $response->assertStatus(200);
    }

    public function test_detail_page_loads_correctly_for_admin()
    {
        $admin = $this->createRootAdmin();
        $targetUser = $this->createNormalUser();
        
        $response = $this->actingAs($admin)->get('/admin/academic-reviewers/' . $targetUser->id);
        $response->assertStatus(200);
        $response->assertSee($targetUser->name);
    }
}
