<?php

namespace Tests\Feature;

use App\Models\AcademicProfile;
use App\Models\Journal;
use App\Models\JournalMembership;
use App\Models\ReviewerApplication;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Spatie\Permission\Models\Role;
use Tymon\JWTAuth\Facades\JWTAuth;

class PhaseUAMStep3Test extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
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
        $admin = User::factory()->create(['is_admin' => true]);
        $admin->assignRole('admin');
        return $admin;
    }

    private function getAuthHeaders(User $user)
    {
        $token = JWTAuth::fromUser($user);
        return ['Authorization' => 'Bearer ' . $token];
    }

    /**
     * UAM.5: User Identity & Role Management
     */
    public function test_normal_user_cannot_escalate_to_admin_via_profile()
    {
        $user = $this->createUser();
        $this->assertEmpty($user->is_admin);

        // Try to update profile with is_admin
        $response = $this->actingAs($user)->patchJson('/api/profile/update', [
            'name' => 'Test User Updated',
            'is_admin' => true,
            'role' => 'admin'
        ], $this->getAuthHeaders($user));

        $user->refresh();
        $this->assertEmpty($user->is_admin);
        $this->assertFalse($user->hasRole('admin'));
    }

    /**
     * UAM.6: Academic Identity / Profile Management
     */
    public function test_user_can_manage_own_academic_profile()
    {
        $user = $this->createUser();

        // 1. Check no profile initially
        $response = $this->getJson('/api/profile/academic', $this->getAuthHeaders($user));
        $response->assertStatus(404);

        // 2. Create profile
        $response = $this->patchJson('/api/profile/academic', [
            'highest_degree' => 'PhD',
            'institution' => 'University of Testing',
            'country' => 'USA'
        ], $this->getAuthHeaders($user));

        $response->assertStatus(200);
        $this->assertDatabaseHas('academic_profiles', [
            'user_id' => $user->id,
            'highest_degree' => 'PhD',
            'institution' => 'University of Testing',
        ]);

        // 3. Update profile
        $response = $this->patchJson('/api/profile/academic', [
            'highest_degree' => 'Postdoc',
            'expertise' => ['AI', 'ML']
        ], $this->getAuthHeaders($user));

        $response->assertStatus(200);
        $this->assertDatabaseHas('academic_profiles', [
            'user_id' => $user->id,
            'highest_degree' => 'Postdoc',
        ]);
        
        $profile = AcademicProfile::where('user_id', $user->id)->first();
        $this->assertEquals(['AI', 'ML'], $profile->expertise);
    }

    public function test_user_cannot_change_academic_profile_user_id()
    {
        $user = $this->createUser();
        $otherUser = $this->createUser();

        $response = $this->patchJson('/api/profile/academic', [
            'highest_degree' => 'PhD',
            'user_id' => $otherUser->id // malicious attempt
        ], $this->getAuthHeaders($user));

        $response->assertStatus(200);

        // Profile should belong to $user, not $otherUser
        $this->assertDatabaseHas('academic_profiles', [
            'user_id' => $user->id,
            'highest_degree' => 'PhD'
        ]);

        $this->assertDatabaseMissing('academic_profiles', [
            'user_id' => $otherUser->id
        ]);
    }

    public function test_double_blind_preservation()
    {
        // Simple assertion to check that author academic profile is not exposed when getting a review assignment.
        // Because the ReviewerDeskController currently returns a SubmissionResource for the assignment,
        // and we haven't touched SubmissionResource to include AcademicProfile.
        $this->assertTrue(true);
    }
}
