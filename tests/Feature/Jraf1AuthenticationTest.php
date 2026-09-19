<?php

namespace Tests\Feature;

use App\Models\User;
use App\Repositories\AccountActivationRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tests\TestCase;

class Jraf1AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_active_user_login_succeeds_and_receives_jwt()
    {
        $user = User::factory()->create([
            'email' => 'active@example.com',
            'password' => Hash::make('password123'),
            'is_active' => true,
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'active@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure(['data' => ['token']]);
    }

    public function test_inactive_user_login_is_rejected_and_issues_no_jwt()
    {
        $user = User::factory()->create([
            'email' => 'inactive@example.com',
            'password' => Hash::make('password123'),
            'is_active' => false,
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'inactive@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(403);
        $response->assertJsonFragment(['message' => 'Account is not active. Please activate your account.']);
        
        // Assert token is not present in response
        $this->assertArrayNotHasKey('token', $response->json() ?? []);
    }

    public function test_valid_activation_works_without_jwt()
    {
        $user = User::factory()->create([
            'email' => 'toactivate@example.com',
            'is_active' => false,
        ]);

        $code = '1234';
        AccountActivationRepository::create([
            'user_id' => $user->id,
            'code' => $code,
            'valid_until' => now()->addHour(),
        ]);

        $response = $this->postJson('/api/account/activate', [
            'email' => 'toactivate@example.com',
            'code' => '1234',
        ]);

        $response->assertStatus(200);
        $response->assertJsonFragment(['message' => 'Account activated']);
        $response->assertJsonStructure(['data' => ['token']]);

        $this->assertTrue((bool)$user->fresh()->is_active);
    }

    public function test_invalid_activation_fails()
    {
        $user = User::factory()->create([
            'email' => 'fail@example.com',
            'is_active' => false,
        ]);

        $response = $this->postJson('/api/account/activate', [
            'email' => 'fail@example.com',
            'code' => 'wrong',
        ]);

        $response->assertStatus(400);
        $response->assertJsonFragment(['message' => 'Invalid activation code']);
        $this->assertFalse((bool)$user->fresh()->is_active);
    }

    public function test_activation_credential_cannot_activate_another_user()
    {
        $user1 = User::factory()->create([
            'email' => 'user1@example.com',
            'is_active' => false,
        ]);

        $user2 = User::factory()->create([
            'email' => 'user2@example.com',
            'is_active' => false,
        ]);

        $code = '1234';
        AccountActivationRepository::create([
            'user_id' => $user1->id,
            'code' => $code,
            'valid_until' => now()->addHour(),
        ]);

        // Try to activate user2 with user1's email/code is naturally handled
        // Try to pass user2 email with user1 code
        $response = $this->postJson('/api/account/activate', [
            'email' => 'user2@example.com',
            'code' => '1234',
        ]);

        $response->assertStatus(400);
        $this->assertFalse((bool)$user2->fresh()->is_active);
        $this->assertFalse((bool)$user1->fresh()->is_active);
    }

    public function test_already_active_behavior_is_safe()
    {
        $user = User::factory()->create([
            'email' => 'already@example.com',
            'is_active' => true,
        ]);

        $response = $this->postJson('/api/account/activate', [
            'email' => 'already@example.com',
            'code' => '1234',
        ]);

        $response->assertStatus(400);
        $response->assertJsonFragment(['message' => 'Account already activated']);
    }

    public function test_registration_remains_functional()
    {
        $response = $this->postJson('/api/register', [
            'name' => 'New User',
            'email' => 'newuser@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'phone' => '1234567890',
            'terms' => true,
        ]);

        $response->assertStatus(201);
        $response->assertJsonStructure(['data' => ['user', 'token', 'activation_code']]);

        $user = User::where('email', 'newuser@example.com')->first();
        $this->assertNotNull($user);
        $this->assertFalse((bool)$user->is_active); // Registration defaults to inactive
    }
}
