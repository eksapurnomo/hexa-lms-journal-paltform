<?php

namespace Tests\Feature;

use App\Models\Journal;
use App\Models\JournalMembershipApplication;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PhaseUAVStep2ViewRenderTest extends TestCase
{
    use RefreshDatabase;

    private function createAdmin()
    {
        return User::factory()->create(['is_admin' => 1]);
    }

    private function createUser()
    {
        return User::factory()->create(['is_admin' => 0]);
    }

    public function test_admin_verification_views_render_successfully()
    {
        $admin = $this->createAdmin();
        $user = $this->createUser();
        $journal = Journal::create([
            'title' => 'Test',
            'slug' => 'test-' . uniqid(),
            'code' => 'T',
            'status' => 'active'
        ]);

        $app = JournalMembershipApplication::create([
            'user_id' => $user->id,
            'journal_id' => $journal->id,
            'requested_role' => 'reviewer',
            'status' => 'under_review'
        ]);

        $this->actingAs($admin)->get(route('admin.membership-verifications.index'))
            ->assertStatus(200);

        $this->actingAs($admin)->get(route('admin.membership-verifications.show', $app->id))
            ->assertStatus(200);
    }

    public function test_user_application_views_render_successfully()
    {
        $user = $this->createUser();
        
        $this->actingAs($user)->get(route('membership-applications.index'))
            ->assertStatus(200);

        $this->actingAs($user)->get(route('membership-applications.create'))
            ->assertStatus(200);
            
        $journal = Journal::create([
            'title' => 'Test',
            'slug' => 'test-' . uniqid(),
            'code' => 'T',
            'status' => 'active'
        ]);

        $app = JournalMembershipApplication::create([
            'user_id' => $user->id,
            'journal_id' => $journal->id,
            'requested_role' => 'reviewer',
            'status' => 'draft'
        ]);

        $this->actingAs($user)->get(route('membership-applications.show', $app->id))
            ->assertStatus(200);
    }
}
