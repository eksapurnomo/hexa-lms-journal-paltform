<?php

namespace Tests\Feature;

use App\Models\Journal;
use App\Models\JournalMembershipApplication;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Jraf4JournalRegistrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
        $this->journal = Journal::create([
            'title' => 'Registration Journal',
            'slug' => 'registration-journal',
            'status' => 'active',
            'user_id' => User::factory()->create()->id,
            'description' => 'Test description',
        ]);
    }

    public function test_spa_registration_route_returns_ok()
    {
        // Vue router handles the actual view, but Laravel should return the SPA entry
        $response = $this->get('/journals/' . $this->journal->slug . '/register');
        $response->assertStatus(200);
    }

    public function test_api_draft_creation_accepts_reviewer_declarations()
    {
        $data = [
            'requested_role' => 'reviewer',
            'academic_type' => 'researcher',
            'declarations' => [
                'confidentiality' => true,
                'coi' => true,
            ],
            'recruitment_source' => 'friend',
        ];

        $response = $this->actingAs($this->user, 'api')->postJson('/api/journals/' . $this->journal->slug . '/membership-application', $data);

        $response->assertStatus(201)
            ->assertJsonPath('data.application.requested_role', 'reviewer')
            ->assertJsonPath('data.application.recruitment_source', 'friend');
        
        $app = JournalMembershipApplication::where('user_id', $this->user->id)->first();
        $this->assertIsArray($app->declarations);
        $this->assertTrue($app->declarations['confidentiality']);
    }

    public function test_api_draft_creation_rejects_unsupported_role()
    {
        $data = [
            'requested_role' => 'superadmin',
            'academic_type' => 'researcher',
        ];

        $response = $this->actingAs($this->user, 'api')->postJson('/api/journals/' . $this->journal->slug . '/membership-application', $data);
        $response->assertStatus(422);
    }
}
