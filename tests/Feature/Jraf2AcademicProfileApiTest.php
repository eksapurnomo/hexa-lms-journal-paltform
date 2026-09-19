<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\AcademicProfile;
use App\Models\Institution;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tests\TestCase;

class Jraf2AcademicProfileApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_get_own_academic_profile()
    {
        $user = User::factory()->create();
        $profile = AcademicProfile::create([
            'user_id' => $user->id,
            'highest_degree' => 'PhD',
            'academic_type' => 'researcher'
        ]);

        $token = JWTAuth::fromUser($user);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/profile/academic');

        $response->assertStatus(200);
        $response->assertJsonFragment(['highest_degree' => 'PhD', 'academic_type' => 'researcher']);
    }

    public function test_authenticated_user_can_patch_own_academic_profile()
    {
        $user = User::factory()->create();
        $institution = Institution::create([
            'name' => 'Test University',
            'source_id' => '123',
        ]);
        $token = JWTAuth::fromUser($user);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->patchJson('/api/profile/academic', [
            'academic_type' => 'lecturer',
            'highest_degree' => 'Master',
            'institution_id' => $institution->id,
            'sinta_id' => '123456',
            'institutional_email' => 'lecturer@university.edu',
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('academic_profiles', [
            'user_id' => $user->id,
            'academic_type' => 'lecturer',
            'highest_degree' => 'Master',
            'institution_id' => $institution->id,
            'sinta_id' => '123456',
            'institutional_email' => 'lecturer@university.edu',
        ]);
    }

    public function test_unauthenticated_access_is_rejected()
    {
        $response = $this->getJson('/api/profile/academic');
        $response->assertStatus(401);

        $patchResponse = $this->patchJson('/api/profile/academic', [
            'academic_type' => 'lecturer',
        ]);
        $patchResponse->assertStatus(401);
    }

    public function test_user_cannot_update_another_users_profile()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        AcademicProfile::create([
            'user_id' => $user2->id,
            'academic_type' => 'researcher'
        ]);

        $token1 = JWTAuth::fromUser($user1);

        // User1 tries to update with arbitrary user_id parameter, but it should ignore it
        // and only update User1's profile.
        $this->withHeaders([
            'Authorization' => 'Bearer ' . $token1,
        ])->patchJson('/api/profile/academic', [
            'user_id' => $user2->id, // Malicious payload
            'academic_type' => 'lecturer',
        ]);

        // User2's profile should remain unchanged
        $this->assertDatabaseHas('academic_profiles', [
            'user_id' => $user2->id,
            'academic_type' => 'researcher' // Not lecturer
        ]);

        // User1 should now have a profile with lecturer
        $this->assertDatabaseHas('academic_profiles', [
            'user_id' => $user1->id,
            'academic_type' => 'lecturer'
        ]);
    }
}
