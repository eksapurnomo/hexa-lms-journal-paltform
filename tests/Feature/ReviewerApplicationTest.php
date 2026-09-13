<?php

namespace Tests\Feature;

use App\Models\Journal;
use App\Models\ReviewerApplication;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ReviewerApplicationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Ensure the admin role exists
        Role::firstOrCreate(['name' => 'admin']);
    }

    public function test_user_can_submit_reviewer_application()
    {
        $user = User::factory()->create();
        $journal = Journal::create([
            'title' => 'Test Journal',
            'slug' => 'test-journal',
            'description' => 'A test journal',
            'created_by' => $user->id,
            'status' => 'active',
        ]);

        $response = $this->actingAs($user)->post(route('reviewer.store'), [
            'journal_id' => $journal->id,
            'affiliation' => 'Test University',
            'department' => 'Computer Science',
            'academic_position' => 'Professor',
            'primary_research_area' => 'AI',
            'years_of_experience' => 5,
            'max_reviews_per_month' => 2,
            'agreed_confidentiality' => '1',
            'agreed_conflict_of_interest' => '1',
            'agreed_guidelines' => '1',
        ]);

        $response->assertRedirect(route('reviewer.application.status'));
        
        $this->assertDatabaseHas('reviewer_applications', [
            'user_id' => $user->id,
            'journal_id' => $journal->id,
            'status' => 'pending',
            'affiliation' => 'Test University',
        ]);
    }

    public function test_duplicate_pending_application_is_rejected()
    {
        $user = User::factory()->create();
        $journal = Journal::create([
            'title' => 'Test Journal 2',
            'slug' => 'test-journal-2',
            'created_by' => $user->id,
        ]);

        ReviewerApplication::create([
            'user_id' => $user->id,
            'journal_id' => $journal->id,
            'status' => 'pending',
            'affiliation' => 'University A',
            'department' => 'Dep',
            'academic_position' => 'Pos',
            'primary_research_area' => 'Area',
            'years_of_experience' => 1,
            'max_reviews_per_month' => 1,
            'agreed_confidentiality' => true,
            'agreed_conflict_of_interest' => true,
            'agreed_guidelines' => true,
        ]);

        // Attempt second submission
        $response = $this->actingAs($user)->post(route('reviewer.store'), [
            'journal_id' => $journal->id,
            'affiliation' => 'University B',
            'department' => 'Computer Science',
            'academic_position' => 'Professor',
            'primary_research_area' => 'AI',
            'years_of_experience' => 5,
            'max_reviews_per_month' => 2,
            'agreed_confidentiality' => '1',
            'agreed_conflict_of_interest' => '1',
            'agreed_guidelines' => '1',
        ]);

        $response->assertRedirect(route('reviewer.application.status'));
        
        $this->assertEquals(1, ReviewerApplication::where('user_id', $user->id)->count());
    }

    public function test_user_can_view_own_application_status()
    {
        $user = User::factory()->create();
        $journal = Journal::create([
            'title' => 'Test Journal 3',
            'slug' => 'test-journal-3',
            'created_by' => $user->id,
        ]);

        ReviewerApplication::create([
            'user_id' => $user->id,
            'journal_id' => $journal->id,
            'status' => 'pending',
            'affiliation' => 'University A',
            'department' => 'Dep',
            'academic_position' => 'Pos',
            'primary_research_area' => 'Area',
            'years_of_experience' => 1,
            'max_reviews_per_month' => 1,
            'agreed_confidentiality' => true,
            'agreed_conflict_of_interest' => true,
            'agreed_guidelines' => true,
        ]);

        $response = $this->actingAs($user)->get(route('reviewer.application.status'));
        $response->assertStatus(200);
        $response->assertSee('PENDING');
    }

    public function test_ordinary_user_cannot_access_management()
    {
        $user = User::factory()->create();
        
        $response = $this->actingAs($user)->get(route('admin.reviewer_applications.index'));
        $response->assertStatus(302);
    }

    public function test_authorized_user_can_accept_pending_application()
    {
        $admin = User::factory()->create(['is_admin' => 1]);
        $admin->assignRole('admin');

        $user = User::factory()->create();
        $journal = Journal::create([
            'title' => 'Test Journal 4',
            'slug' => 'test-journal-4',
            'created_by' => $user->id,
        ]);

        $app = ReviewerApplication::create([
            'user_id' => $user->id,
            'journal_id' => $journal->id,
            'status' => 'pending',
            'affiliation' => 'University A',
            'department' => 'Dep',
            'academic_position' => 'Pos',
            'primary_research_area' => 'Area',
            'years_of_experience' => 1,
            'max_reviews_per_month' => 1,
            'agreed_confidentiality' => true,
            'agreed_conflict_of_interest' => true,
            'agreed_guidelines' => true,
        ]);

        $response = $this->actingAs($admin)->post(route('admin.reviewer_applications.accept', $app->id));
        $response->assertRedirect();
        
        $app->refresh();
        $this->assertEquals('accepted', $app->status);
        $this->assertEquals($admin->id, $app->reviewed_by);
        $this->assertNotNull($app->reviewed_at);
    }

    public function test_authorized_user_can_deny_pending_application()
    {
        $admin = User::factory()->create(['is_admin' => 1]);
        $admin->assignRole('admin');

        $user = User::factory()->create();
        $journal = Journal::create([
            'title' => 'Test Journal 5',
            'slug' => 'test-journal-5',
            'created_by' => $user->id,
        ]);

        $app = ReviewerApplication::create([
            'user_id' => $user->id,
            'journal_id' => $journal->id,
            'status' => 'pending',
            'affiliation' => 'University A',
            'department' => 'Dep',
            'academic_position' => 'Pos',
            'primary_research_area' => 'Area',
            'years_of_experience' => 1,
            'max_reviews_per_month' => 1,
            'agreed_confidentiality' => true,
            'agreed_conflict_of_interest' => true,
            'agreed_guidelines' => true,
        ]);

        $response = $this->actingAs($admin)->post(route('admin.reviewer_applications.deny', $app->id), [
            'denial_reason' => 'Does not meet requirements.'
        ]);
        
        $response->assertRedirect();
        
        $app->refresh();
        $this->assertEquals('denied', $app->status);
        $this->assertEquals('Does not meet requirements.', $app->denial_reason);
        $this->assertEquals($admin->id, $app->reviewed_by);
        $this->assertNotNull($app->reviewed_at);
    }

    public function test_terminal_state_cannot_be_re_decided()
    {
        $admin = User::factory()->create(['is_admin' => 1]);
        $admin->assignRole('admin');

        $user = User::factory()->create();
        $journal = Journal::create([
            'title' => 'Test Journal 6',
            'slug' => 'test-journal-6',
            'created_by' => $user->id,
        ]);

        $app = ReviewerApplication::create([
            'user_id' => $user->id,
            'journal_id' => $journal->id,
            'status' => 'accepted',
            'affiliation' => 'University A',
            'department' => 'Dep',
            'academic_position' => 'Pos',
            'primary_research_area' => 'Area',
            'years_of_experience' => 1,
            'max_reviews_per_month' => 1,
            'agreed_confidentiality' => true,
            'agreed_conflict_of_interest' => true,
            'agreed_guidelines' => true,
        ]);

        // Try to deny an accepted application
        $response = $this->actingAs($admin)->post(route('admin.reviewer_applications.deny', $app->id), [
            'denial_reason' => 'Changed my mind.'
        ]);
        
        $app->refresh();
        $this->assertEquals('accepted', $app->status); // Status unchanged
    }

    public function test_user_cannot_accept_own_application()
    {
        $user = User::factory()->create();
        $journal = Journal::create([
            'title' => 'Test Journal 7',
            'slug' => 'test-journal-7',
            'created_by' => $user->id,
        ]);

        $app = ReviewerApplication::create([
            'user_id' => $user->id,
            'journal_id' => $journal->id,
            'status' => 'pending',
            'affiliation' => 'University A',
            'department' => 'Dep',
            'academic_position' => 'Pos',
            'primary_research_area' => 'Area',
            'years_of_experience' => 1,
            'max_reviews_per_month' => 1,
            'agreed_confidentiality' => true,
            'agreed_conflict_of_interest' => true,
            'agreed_guidelines' => true,
        ]);

        // Attempt to hit the admin endpoint
        $response = $this->actingAs($user)->post(route('admin.reviewer_applications.accept', $app->id));
        $response->assertStatus(302);
        
        $app->refresh();
        $this->assertEquals('pending', $app->status);
    }
}
