<?php

namespace Tests\Feature;

use App\Models\AcademicProfile;
use App\Models\Journal;
use App\Models\JournalMembership;
use App\Models\ReviewAssignment;
use App\Models\ReviewerApplication;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Spatie\Permission\Models\Role;

class PhaseUAMStep2Test extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        if (!Role::where('name', 'admin')->exists()) {
            Role::create(['name' => 'admin']);
        }
    }

    private function createJournal()
    {
        return Journal::create([
            'title' => 'Test Journal ' . uniqid(),
            'slug' => 'test-journal-' . uniqid(),
            'status' => 'active'
        ]);
    }

    private function createUser()
    {
        return User::factory()->create();
    }

    private function addMember(Journal $journal, User $user, string $role, string $status = 'active')
    {
        return JournalMembership::create([
            'journal_id' => $journal->id,
            'user_id' => $user->id,
            'role' => $role,
            'status' => $status,
        ]);
    }

    private function createSubmission(Journal $journal, User $author)
    {
        return Submission::forceCreate([
            'journal_id' => $journal->id,
            'created_by' => $author->id,
            'title' => 'Test',
            'abstract' => 'Test abstract',
            'status' => 'review_pending'
        ]);
    }

    /**
     * Test A — Academic Profile reused by Reviewer
     * Test B — Reviewer Application exists as canonical workflow
     */
    public function test_reviewer_application_syncs_academic_profile()
    {
        $user = $this->createUser();
        $journal = $this->createJournal();

        $response = $this->actingAs($user)->post(route('reviewer.store'), [
            'journal_id' => $journal->id,
            'affiliation' => 'Test Uni',
            'department' => 'CS',
            'academic_position' => 'Prof',
            'orcid' => '0000-0002-1825-0097',
            'academic_url' => 'http://example.com',
            'primary_research_area' => 'AI',
            'research_keywords' => 'Machine Learning',
            'expertise' => 'Algorithms',
            'years_of_experience' => 5,
            'previous_experience' => 'None',
            'max_reviews_per_month' => 2,
            'agreed_confidentiality' => 'on',
            'agreed_conflict_of_interest' => 'on',
            'agreed_guidelines' => 'on',
        ]);

        $response->assertRedirect();
        
        $this->assertDatabaseHas('reviewer_applications', [
            'user_id' => $user->id,
            'status' => ReviewerApplication::STATUS_PENDING,
        ]);

        $this->assertDatabaseHas('academic_profiles', [
            'user_id' => $user->id,
            'institution' => 'Test Uni',
            'department' => 'CS',
        ]);
    }

    /**
     * Test C — Pending reviewer is not active
     * Test D — Approved reviewer
     * Test E — Rejected reviewer
     * Test F — Inactive membership
     */
    public function test_reviewer_eligibility()
    {
        $journal = $this->createJournal();
        $editor = $this->createUser();
        $this->addMember($journal, $editor, 'editor');
        
        $submission = Submission::forceCreate([
            'journal_id' => $journal->id,
            'created_by' => $this->createUser()->id,
            'editor_id' => $editor->id,
            'title' => 'Test',
            'abstract' => 'Test abstract',
            'status' => 'review_pending'
        ]);

        // Reviewer C: Pending
        $pendingUser = $this->createUser();
        $this->addMember($journal, $pendingUser, 'reviewer');
        ReviewerApplication::create([
            'user_id' => $pendingUser->id, 'journal_id' => $journal->id,
            'status' => ReviewerApplication::STATUS_PENDING,
            'affiliation' => 'A', 'department' => 'D', 'academic_position' => 'P',
            'primary_research_area' => 'R', 'years_of_experience' => 1,
        ]);
        AcademicProfile::factory()->create(['user_id' => $pendingUser->id]);

        // Reviewer D: Approved & Active Membership
        $approvedUser = $this->createUser();
        $this->addMember($journal, $approvedUser, 'reviewer');
        ReviewerApplication::create([
            'user_id' => $approvedUser->id, 'journal_id' => $journal->id,
            'status' => ReviewerApplication::STATUS_ACCEPTED,
            'affiliation' => 'A', 'department' => 'D', 'academic_position' => 'P',
            'primary_research_area' => 'R', 'years_of_experience' => 1,
        ]);
        AcademicProfile::factory()->create(['user_id' => $approvedUser->id]);

        // Reviewer E: Rejected
        $rejectedUser = $this->createUser();
        $this->addMember($journal, $rejectedUser, 'reviewer');
        ReviewerApplication::create([
            'user_id' => $rejectedUser->id, 'journal_id' => $journal->id,
            'status' => ReviewerApplication::STATUS_DENIED,
            'affiliation' => 'A', 'department' => 'D', 'academic_position' => 'P',
            'primary_research_area' => 'R', 'years_of_experience' => 1,
        ]);
        AcademicProfile::factory()->create(['user_id' => $rejectedUser->id]);

        // Reviewer F: Approved but Inactive Membership
        $inactiveUser = $this->createUser();
        $this->addMember($journal, $inactiveUser, 'reviewer', 'inactive');
        ReviewerApplication::create([
            'user_id' => $inactiveUser->id, 'journal_id' => $journal->id,
            'status' => ReviewerApplication::STATUS_ACCEPTED,
            'affiliation' => 'A', 'department' => 'D', 'academic_position' => 'P',
            'primary_research_area' => 'R', 'years_of_experience' => 1,
        ]);
        AcademicProfile::factory()->create(['user_id' => $inactiveUser->id]);

        $response = $this->actingAs($editor)->getJson("/api/editorial/submissions/{$submission->id}/eligible-reviewers");
        
        $data = collect($response->json('data'));
        
        $this->assertFalse($data->contains('id', $pendingUser->id));
        $this->assertTrue($data->contains('id', $approvedUser->id));
        $this->assertFalse($data->contains('id', $rejectedUser->id));
        $this->assertFalse($data->contains('id', $inactiveUser->id));
    }

    /**
     * Test G, H, I, J, N - Already covered by UAM Step 1 (JournalMembershipController tests)
     * We'll just verify Admin accepting application creates JournalMembership
     */
    public function test_admin_accept_application_creates_membership()
    {
        $admin = $this->createUser();
        $admin->is_admin = true;
        $admin->assignRole('admin');
        
        $user = $this->createUser();
        $journal = $this->createJournal();
        
        $application = ReviewerApplication::create([
            'user_id' => $user->id, 'journal_id' => $journal->id,
            'status' => ReviewerApplication::STATUS_PENDING,
            'affiliation' => 'A', 'department' => 'D', 'academic_position' => 'P',
            'primary_research_area' => 'R', 'years_of_experience' => 1,
        ]);

        $response = $this->actingAs($admin)->post(route('admin.reviewer_applications.accept', $application->id));
        $response->assertRedirect();
        
        $this->assertDatabaseHas('reviewer_applications', [
            'id' => $application->id,
            'status' => ReviewerApplication::STATUS_ACCEPTED
        ]);

        $this->assertDatabaseHas('journal_memberships', [
            'user_id' => $user->id,
            'journal_id' => $journal->id,
            'role' => 'reviewer',
            'status' => 'pending'
        ]);
    }

    /**
     * Test K — Reviewer Pool does not create ReviewAssignment
     */
    public function test_adding_reviewer_pool_does_not_create_assignment()
    {
        $user = $this->createUser();
        $journal = $this->createJournal();
        
        $this->addMember($journal, $user, 'reviewer');
        
        $this->assertDatabaseMissing('review_assignments', [
            'reviewer_id' => $user->id
        ]);
    }
}
