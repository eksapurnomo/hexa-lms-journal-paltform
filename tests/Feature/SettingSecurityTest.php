<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class SettingSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        if (config('database.default') !== 'mysql' || config('database.connections.mysql.database') !== 'readylms_testing') {
            $this->markTestSkipped('Not using testing database.');
        }
    }

    private function createAdmin()
    {
        return User::factory()->create(['is_admin' => true]);
    }

    private function createNonAdmin()
    {
        return User::factory()->create(['is_admin' => false]);
    }

    public function test_unauthenticated_cannot_access_settings()
    {
        $this->get('/admin/setting')->assertRedirect();
        $this->put('/admin/setting', [])->assertRedirect();
    }

    public function test_non_admin_cannot_access_settings()
    {
        $user = $this->createNonAdmin();
        // The admin middleware might redirect non-admins
        $response = $this->actingAs($user)->get('/admin/setting');
        $this->assertTrue($response->isRedirect() || $response->isForbidden());
    }

    public function test_admin_can_access_empty_settings()
    {
        $admin = $this->createAdmin();
        Setting::truncate();

        $this->assertDatabaseCount('settings', 0);
        $this->actingAs($admin)->get('/admin/setting')->assertStatus(200);
    }

    public function test_first_save_creates_singleton_settings()
    {
        $admin = $this->createAdmin();
        Setting::truncate();

        $this->assertDatabaseCount('settings', 0);

        $payload = [
            'app_name' => 'HexaLMS Test',
            'app_currency' => 'USD',
            'app_currency_symbol' => '$',
            'app_timezone' => 'UTC',
            'app_minimum_amount' => 10,
            'footer_text' => 'Test Footer',
            'footer_contact_number' => '+123456789',
            'footer_support_mail' => 'support@hexalms.local',
            'footer_description' => 'Test Desc',
            'currency_position' => 'Left',
            'social_links' => [],
        ];

        $this->actingAs($admin)->put('/admin/setting', $payload)->assertRedirect(route('setting.index'));

        $this->assertDatabaseCount('settings', 1);
        $setting = Setting::first();
        $this->assertEquals('Test Footer', $setting->footer_text);
    }

    public function test_repeated_save_prevents_duplicate_records()
    {
        $admin = $this->createAdmin();
        Setting::truncate();

        $payload = [
            'app_name' => 'HexaLMS Test',
            'app_currency' => 'USD',
            'app_currency_symbol' => '$',
            'app_timezone' => 'UTC',
            'app_minimum_amount' => 10,
            'footer_text' => 'First Footer',
            'footer_contact_number' => '+123456789',
            'footer_support_mail' => 'support@hexalms.local',
            'footer_description' => 'Test Desc',
            'currency_position' => 'Left',
            'social_links' => [],
        ];

        $this->actingAs($admin)->put('/admin/setting', $payload)->assertRedirect(route('setting.index'));
        $this->assertDatabaseCount('settings', 1);

        $payload['footer_text'] = 'Second Footer';
        $this->actingAs($admin)->put('/admin/setting', $payload)->assertRedirect(route('setting.index'));
        $this->assertDatabaseCount('settings', 1);

        $setting = Setting::first();
        $this->assertEquals('Second Footer', $setting->footer_text);
    }

    public function test_existing_settings_render_correctly()
    {
        $admin = $this->createAdmin();
        Setting::truncate();

        $setting = Setting::create([
            'footer_text' => 'Existing Footer',
            'currency_position' => 'Right',
        ]);

        $this->actingAs($admin)->get('/admin/setting')->assertStatus(200)->assertSee('Existing Footer');
    }
}
