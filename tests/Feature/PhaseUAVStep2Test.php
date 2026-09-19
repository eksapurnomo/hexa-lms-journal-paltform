<?php

namespace Tests\Feature;

use App\Models\AcademicProfile;
use App\Models\Journal;
use App\Models\JournalMembership;
use App\Models\JournalMembershipApplication;
use App\Models\User;
use App\Models\VerificationEvidence;
use App\Models\ReviewerApplication;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PhaseUAVStep2Test extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
    }

    private function createAdmin()
    {
        $admin = User::factory()->create(['is_admin' => 1]);
        return $admin;
    }

    private function createUser()
    {
        return User::factory()->create(['is_admin' => 0]);
    }

    private function createJournal()
    {
        return Journal::create([
            'title' => 'Test Journal',
            'slug' => 'test-journal-' . uniqid(),
            'code' => 'TJ',
            'description' => 'Test',
            'status' => 'active'
        ]);
    }

    public function test_user_can_create_draft_application_with_optional_institutional_email()
    {
        $user = $this->createUser();
        $journal = $this->createJournal();

        $response = $this->actingAs($user)->post(route('membership-applications.store'), [
            'journal_id' => $journal->id,
            'requested_role' => 'reviewer',
            'academic_type' => 'lecturer',
            'highest_degree' => 'PhD',
            'institutional_email' => 'test@university.edu',
        ]);

        $response->assertRedirect();
        
        $this->assertDatabaseHas('journal_membership_applications', [
            'user_id' => $user->id,
            'journal_id' => $journal->id,
            'requested_role' => 'reviewer',
            'status' => 'draft',
        ]);

        $this->assertDatabaseHas('academic_profiles', [
            'user_id' => $user->id,
            'highest_degree' => 'PhD',
            'institutional_email' => 'test@university.edu',
        ]);

        // Original email is untouched
        $this->assertNotEquals('test@university.edu', $user->fresh()->email);
    }

    public function test_user_cannot_force_status_during_draft_creation()
    {
        $user = $this->createUser();
        $journal = $this->createJournal();

        $response = $this->actingAs($user)->post(route('membership-applications.store'), [
            'journal_id' => $journal->id,
            'requested_role' => 'reviewer',
            'academic_type' => 'lecturer',
            'status' => 'approved', // Should be ignored
        ]);

        $this->assertDatabaseHas('journal_membership_applications', [
            'user_id' => $user->id,
            'status' => 'draft', // Forced to draft by controller
        ]);
    }

    public function test_user_can_upload_evidence_and_rejects_executables()
    {
        $user = $this->createUser();
        $app = JournalMembershipApplication::create([
            'user_id' => $user->id,
            'journal_id' => $this->createJournal()->id,
            'requested_role' => 'reviewer',
            'status' => 'draft'
        ]);

        // Upload valid PDF
        $file = UploadedFile::fake()->create('document.pdf', 100, 'application/pdf');
        $response = $this->actingAs($user)->post(route('verification-evidence.store', $app->id), [
            'category' => 'identity',
            'document' => $file
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseCount('verification_evidences', 1);

        $evidence = VerificationEvidence::first();
        $this->assertStringStartsWith('private/verifications/', $evidence->file_path);
        Storage::disk('local')->assertExists($evidence->file_path);

        // Upload invalid executable
        $exe = UploadedFile::fake()->create('malware.exe', 100, 'application/x-msdownload');
        $response2 = $this->actingAs($user)->post(route('verification-evidence.store', $app->id), [
            'category' => 'academic_credential',
            'document' => $exe
        ]);

        $response2->assertSessionHasErrors('document');
        $this->assertDatabaseCount('verification_evidences', 1);
    }

    public function test_private_evidence_download_authorization()
    {
        $owner = $this->createUser();
        $otherUser = $this->createUser();
        $admin = $this->createAdmin();
        $app = JournalMembershipApplication::create([
            'user_id' => $owner->id,
            'journal_id' => $this->createJournal()->id,
            'requested_role' => 'reviewer',
            'status' => 'draft'
        ]);

        $file = UploadedFile::fake()->create('doc.pdf', 100, 'application/pdf');
        $path = $file->storeAs('private/verifications', 'doc_random.pdf', 'local');
        
        $evidence = VerificationEvidence::create([
            'user_id' => $owner->id,
            'journal_membership_application_id' => $app->id,
            'category' => 'identity',
            'file_path' => $path,
            'status' => 'pending'
        ]);

        // Unauthenticated
        $this->get(route('verification-evidence.download', $evidence->id))->assertRedirect();

        // Other user
        $this->actingAs($otherUser)->get(route('verification-evidence.download', $evidence->id))->assertForbidden();

        // Owner
        $this->actingAs($owner)->get(route('verification-evidence.download', $evidence->id))->assertOk();

        // Admin
        $this->actingAs($admin)->get(route('admin.verification-evidence.download', $evidence->id))->assertOk();
    }

    public function test_user_can_submit_application()
    {
        $user = $this->createUser();
        $app = JournalMembershipApplication::create([
            'user_id' => $user->id,
            'journal_id' => $this->createJournal()->id,
            'requested_role' => 'reviewer',
            'status' => 'draft'
        ]);

        $response = $this->actingAs($user)->post(route('membership-applications.submit', $app->id));
        $response->assertSessionHasNoErrors();
        
        $this->assertEquals('submitted', $app->fresh()->status);
        $this->assertNotNull($app->fresh()->submitted_at);
    }

    public function test_admin_can_update_status_and_approve_atomically()
    {
        $admin = $this->createAdmin();
        $user = $this->createUser();
        $journal = $this->createJournal();
        
        $app = JournalMembershipApplication::create([
            'user_id' => $user->id,
            'journal_id' => $journal->id,
            'requested_role' => 'editor',
            'status' => 'under_review'
        ]);

        $response = $this->actingAs($admin)->post(route('admin.membership-verifications.updateStatus', $app->id), [
            'status' => 'approved',
            'reviewer_note' => 'Looks good'
        ]);

        $response->assertSessionHasNoErrors();

        // Check atomic result
        $app->refresh();
        $this->assertEquals('approved', $app->status);
        $this->assertEquals($admin->id, $app->reviewed_by);
        $this->assertNotNull($app->reviewed_at);

        // Check Membership Activated
        $this->assertDatabaseHas('journal_memberships', [
            'user_id' => $user->id,
            'journal_id' => $journal->id,
            'role' => 'editor',
            'status' => 'active'
        ]);
    }

    public function test_approval_upgrades_existing_pending_membership_without_duplication()
    {
        $admin = $this->createAdmin();
        $user = $this->createUser();
        $journal = $this->createJournal();
        
        // Existing pending membership
        JournalMembership::create([
            'user_id' => $user->id,
            'journal_id' => $journal->id,
            'role' => 'reviewer',
            'status' => 'pending'
        ]);

        $app = JournalMembershipApplication::create([
            'user_id' => $user->id,
            'journal_id' => $journal->id,
            'requested_role' => 'reviewer',
            'status' => 'under_review'
        ]);

        $this->actingAs($admin)->post(route('admin.membership-verifications.updateStatus', $app->id), [
            'status' => 'approved'
        ]);

        // Assert only 1 membership exists and it is active
        $this->assertDatabaseCount('journal_memberships', 1);
        $this->assertDatabaseHas('journal_memberships', [
            'user_id' => $user->id,
            'status' => 'active'
        ]);
    }

    public function test_approval_rejected_if_active_duplicate_exists()
    {
        $admin = $this->createAdmin();
        $user = $this->createUser();
        $journal = $this->createJournal();
        
        // Existing active membership
        JournalMembership::create([
            'user_id' => $user->id,
            'journal_id' => $journal->id,
            'role' => 'editor',
            'status' => 'active'
        ]);

        $app = JournalMembershipApplication::create([
            'user_id' => $user->id,
            'journal_id' => $journal->id,
            'requested_role' => 'editor',
            'status' => 'under_review'
        ]);

        $response = $this->actingAs($admin)->post(route('admin.membership-verifications.updateStatus', $app->id), [
            'status' => 'approved'
        ]);

        $response->assertSessionHas('error');
        $this->assertEquals('under_review', $app->fresh()->status); // Rolled back
        $this->assertDatabaseCount('journal_memberships', 1); // No duplicate created
    }

    public function test_user_can_have_multiple_active_roles_across_journals()
    {
        $admin = $this->createAdmin();
        $user = $this->createUser();
        $journal1 = $this->createJournal();
        $journal2 = Journal::create(['title' => 'J2', 'slug' => 'j2-slug', 'code' => 'J2', 'status' => 'active']);

        JournalMembership::create([
            'user_id' => $user->id,
            'journal_id' => $journal1->id,
            'role' => 'editor',
            'status' => 'active'
        ]);

        $app = JournalMembershipApplication::create([
            'user_id' => $user->id,
            'journal_id' => $journal2->id,
            'requested_role' => 'reviewer',
            'status' => 'under_review'
        ]);

        $response = $this->actingAs($admin)->post(route('admin.membership-verifications.updateStatus', $app->id), [
            'status' => 'approved'
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseCount('journal_memberships', 2);
    }

    public function test_rejection_does_not_activate_membership()
    {
        $admin = $this->createAdmin();
        $user = $this->createUser();
        $journal = $this->createJournal();

        $app = JournalMembershipApplication::create([
            'user_id' => $user->id,
            'journal_id' => $journal->id,
            'requested_role' => 'editor',
            'status' => 'under_review'
        ]);

        $this->actingAs($admin)->post(route('admin.membership-verifications.updateStatus', $app->id), [
            'status' => 'rejected'
        ]);

        $this->assertEquals('rejected', $app->fresh()->status);
        $this->assertDatabaseCount('journal_memberships', 0);
    }

    public function test_needs_revision_resubmission()
    {
        $admin = $this->createAdmin();
        $user = $this->createUser();
        $journal = $this->createJournal();

        $app = JournalMembershipApplication::create([
            'user_id' => $user->id,
            'journal_id' => $journal->id,
            'requested_role' => 'editor',
            'status' => 'under_review'
        ]);

        $this->actingAs($admin)->post(route('admin.membership-verifications.updateStatus', $app->id), [
            'status' => 'needs_revision',
            'reviewer_note' => 'Please upload KTP'
        ]);

        $this->assertEquals('needs_revision', $app->fresh()->status);

        $this->actingAs($user)->post(route('membership-applications.submit', $app->id));
        
        $this->assertEquals('submitted', $app->fresh()->status);
    }

    public function test_existing_reviewer_application_workflow_remains_intact()
    {
        $user = $this->createUser();
        $journal = $this->createJournal();

        $response = $this->actingAs($user)->post(route('reviewer.store'), [
            'journal_id' => $journal->id,
            'affiliation' => 'Univ',
            'department' => 'Dept',
            'academic_position' => 'Prof',
            'primary_research_area' => 'CS',
            'years_of_experience' => 5,
            'max_reviews_per_month' => 2,
            'agreed_confidentiality' => '1',
            'agreed_conflict_of_interest' => '1',
            'agreed_guidelines' => '1',
            'available_for_review' => '1',
        ]);

        $this->assertDatabaseHas('reviewer_applications', [
            'user_id' => $user->id,
            'journal_id' => $journal->id,
            'status' => 'pending'
        ]);
        
        // Ensure academic profile was also synced via canonical flow
        $this->assertDatabaseHas('academic_profiles', [
            'user_id' => $user->id,
            'institution' => 'Univ'
        ]);
    }
}
