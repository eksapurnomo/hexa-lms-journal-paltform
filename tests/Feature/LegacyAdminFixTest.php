<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Setting;
use App\Models\PaymentGateway;
use App\Models\Page;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class LegacyAdminFixTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Since we aren't seeding, the DB is clean
    }

    public function test_editorial_desk_auth_middleware_bypasses_jwt()
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'web');

        $response = $this->get('/api/editorial/submissions');
        $response->assertStatus(200); // Or 403 if they don't have permission, but not 500!
        $this->assertNotEquals(500, $response->getStatusCode());
    }

    public function test_payment_gateway_safe_null()
    {
        $user = User::factory()->create(['role_id' => 1]);
        $this->actingAs($user, 'web');

        $response = $this->get('/admin/payment-gateway');
        $response->assertStatus(200);
    }

    public function test_settings_logo_upload_with_null_fields()
    {
        $user = User::factory()->create(['role_id' => 1]);
        $this->actingAs($user, 'web');
        
        Storage::fake('public');
        $file = UploadedFile::fake()->image('logo.png');

        $response = $this->put('/admin/setting', [
            'logo' => $file,
            'app_name' => 'ReadyLMS',
            // Omit footer_text to ensure default handles it
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('settings', [
            'app_name' => 'ReadyLMS',
            'footer_text' => '',
        ]);
        
        $this->assertDatabaseCount('media', 1);
    }

    public function test_page_4_returns_404()
    {
        $user = User::factory()->create(['role_id' => 1]);
        $this->actingAs($user, 'web');

        $response = $this->get('/admin/page/4/edit');
        $response->assertStatus(404);
    }
}
