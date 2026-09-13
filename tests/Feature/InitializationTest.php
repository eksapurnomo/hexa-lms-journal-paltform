<?php

namespace Tests\Feature;

use App\Models\PaymentGateway;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;
use App\Models\Page;
use App\Models\Media;

class InitializationTest extends TestCase
{
    use RefreshDatabase;

    public function test_empty_foundation_initialization()
    {
        $this->artisan('hexa:initialize')
            ->assertExitCode(0);

        // Assert Settings
        $this->assertEquals(1, Setting::count());
        $setting = Setting::first();
        $this->assertEquals('', $setting->footer_text);
        $this->assertEquals('left', $setting->currency_position);

        // Assert Roles
        $this->assertTrue(Role::where('name', 'admin')->exists());
        $this->assertTrue(Role::where('name', 'instructor')->exists());

        // Assert Permissions
        $this->assertTrue(Permission::count() > 0);

        // Assert Role-Permission Mappings
        $admin = Role::where('name', 'admin')->first();
        $this->assertTrue($admin->permissions()->count() > 0);

        // Assert Payment Gateways
        $this->assertEquals(5, PaymentGateway::count());
        $stripe = PaymentGateway::where('name', 'stripe')->first();
        $this->assertFalse((bool)$stripe->is_active);
        $this->assertEquals('{}', $stripe->config);
    }

    public function test_existing_settings_remain_unchanged()
    {
        Setting::create([
            'footer_text' => 'Custom Footer',
            'footer_contact_number' => '12345',
            'footer_support_mail' => 'test@test.com',
            'footer_description' => 'Desc',
            'currency_position' => 'right'
        ]);

        $this->artisan('hexa:initialize')->assertExitCode(0);

        $this->assertEquals(1, Setting::count());
        $setting = Setting::first();
        $this->assertEquals('Custom Footer', $setting->footer_text);
        $this->assertEquals('right', $setting->currency_position);
    }

    public function test_existing_gateway_remains_unchanged()
    {
        PaymentGateway::create([
            'name' => 'stripe',
            'is_active' => true,
            'config' => json_encode(['secret' => '123']),
            'type' => 'live'
        ]);

        $this->artisan('hexa:initialize')->assertExitCode(0);

        $stripe = PaymentGateway::where('name', 'stripe')->first();
        $this->assertTrue((bool)$stripe->is_active);
        $this->assertEquals('{"secret":"123"}', $stripe->config);
    }

    public function test_existing_role_remains_unchanged()
    {
        $admin = Role::create(['name' => 'admin', 'guard_name' => 'web']);
        // Do not assign permissions

        $this->artisan('hexa:initialize')->assertExitCode(0);

        $admin->refresh();
        $this->assertEquals(0, $admin->permissions()->count());
    }

    public function test_no_users_pages_media_or_credentials_created()
    {
        $this->artisan('hexa:initialize')->assertExitCode(0);

        $this->assertEquals(0, User::count());
        $this->assertEquals(0, Page::count());
        $this->assertEquals(0, Media::count());

        $gateways = PaymentGateway::all();
        foreach ($gateways as $gateway) {
            $this->assertEquals('{}', $gateway->config);
        }
    }

    public function test_repeat_execution_is_idempotent()
    {
        $this->artisan('hexa:initialize')->assertExitCode(0);
        $this->artisan('hexa:initialize')->assertExitCode(0);

        $this->assertEquals(1, Setting::count());
        $this->assertEquals(5, PaymentGateway::count());
        $this->assertEquals(2, Role::count());
    }

    public function test_dry_run()
    {
        $this->artisan('hexa:initialize', ['--dry-run' => true])
            ->expectsOutput('DRY RUN ENABLED - No database changes will be made.')
            ->assertExitCode(0);

        $this->assertEquals(0, Setting::count());
        $this->assertEquals(0, PaymentGateway::count());
        $this->assertEquals(0, Role::count());
        $this->assertEquals(0, Permission::count());
    }
}
