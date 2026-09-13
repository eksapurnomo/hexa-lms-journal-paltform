<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminLoginTest extends TestCase
{
    use RefreshDatabase;

    /** Admin login page loads. */
    public function test_admin_login_page_returns_200(): void
    {
        $response = $this->get('/admin/login');
        $response->assertStatus(200);
    }

    /** is_admin=true user is redirected to admin.dashboard after login. */
    public function test_admin_is_redirected_to_dashboard(): void
    {
        $admin = User::create([
            'name'              => 'Test Admin',
            'email'             => 'test-admin@hexalms.local',
            'password'          => Hash::make('password'),
            'is_admin'          => true,
            'is_active'         => true,
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($admin)->get('/admin/login');
        $response->assertRedirect(route('admin.dashboard'));
    }

    /** Authenticated admin can access /admin dashboard. */
    public function test_admin_can_access_dashboard(): void
    {
        $admin = User::create([
            'name'              => 'Test Admin',
            'email'             => 'test-admin2@hexalms.local',
            'password'          => Hash::make('password'),
            'is_admin'          => true,
            'is_active'         => true,
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($admin)->get('/admin');
        $response->assertStatus(200);
    }

    /** Non-admin unauthenticated user is blocked from /admin. */
    public function test_unauthenticated_cannot_access_admin_dashboard(): void
    {
        $response = $this->get('/admin');
        // Should redirect to the admin login page
        $response->assertRedirect('/admin/login');
    }
}
