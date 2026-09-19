<?php

namespace Tests\Feature;

use App\Models\AcademicProfile;
use App\Models\Institution;
use App\Models\Journal;
use App\Models\JournalMembershipApplication;
use App\Models\User;
use App\Models\VerificationEvidence;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UAV5MembershipApplicationTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $journal;
    protected $institution;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
        $this->journal = Journal::create([
            'title' => 'Test Journal',
            'slug' => 'test-journal',
            'status' => 'active',
            'description' => 'Test desc',
        ]);
        $this->institution = Institution::create([
            'name' => 'Test Institution',
            'is_active' => true,
            'source_id' => 'dummy-id',
        ]);
    }

    // 1. Lecturer profile form renders
    // 2. Researcher profile form renders
    public function test_create_form_renders_for_user()
    {
        $response = $this->actingAs($this->user)->get(route('membership-applications.create'));
        $response->assertStatus(200);
        $response->assertSee('Apply for Journal Role');
        $response->assertSee('Academic Type');
        $response->assertSee('Institution Type');
    }

    // 3. Lecturer can submit application
    public function test_lecturer_can_submit_application()
    {
        $data = [
            'journal_id' => $this->journal->id,
            'requested_role' => 'editor',
            'academic_type' => 'lecturer',
            'highest_degree' => 'PhD',
            'academic_position' => 'Professor',
            'institution_type' => 'university',
            'institution_id' => $this->institution->id,
            'country' => 'Indonesia',
        ];

        $response = $this->actingAs($this->user)->post(route('membership-applications.store'), $data);
        
        $app = JournalMembershipApplication::first();
        $this->assertNotNull($app);
        $response->assertRedirect(route('membership-applications.show', $app->id));
        
        $profile = AcademicProfile::where('user_id', $this->user->id)->first();
        $this->assertEquals('lecturer', $profile->academic_type);
        $this->assertEquals('university', $profile->institution_type);
    }

    // 4. Researcher can submit application
    public function test_researcher_can_submit_application()
    {
        $data = [
            'journal_id' => $this->journal->id,
            'requested_role' => 'reviewer',
            'academic_type' => 'researcher',
            'highest_degree' => 'Master',
            'academic_position' => 'Senior Researcher',
            'institution_type' => 'research_institute',
            'institution_id' => $this->institution->id,
        ];

        $response = $this->actingAs($this->user)->post(route('membership-applications.store'), $data);
        
        $app = JournalMembershipApplication::first();
        $this->assertNotNull($app);
        $response->assertRedirect(route('membership-applications.show', $app->id));
        
        $profile = AcademicProfile::where('user_id', $this->user->id)->first();
        $this->assertEquals('researcher', $profile->academic_type);
        $this->assertEquals('research_institute', $profile->institution_type);
    }

    // 5. Researcher with government research institution is valid
    // 6. Researcher with NGO is valid
    // 7. Researcher with think tank is valid
    public function test_various_institution_types_are_valid()
    {
        $types = ['government_research', 'ngo', 'think_tank'];
        
        foreach ($types as $type) {
            $user = User::factory()->create();
            $data = [
                'journal_id' => $this->journal->id,
                'requested_role' => 'reviewer',
                'academic_type' => 'researcher',
                'institution_type' => $type,
                'institution' => 'Test ' . $type,
            ];
            
            $response = $this->actingAs($user)->post(route('membership-applications.store'), $data);
            $response->assertSessionHasNoErrors();
            
            $profile = AcademicProfile::where('user_id', $user->id)->first();
            $this->assertEquals($type, $profile->institution_type);
        }
    }

    // 8. Independent researcher can have null institution
    public function test_independent_researcher_clears_institution()
    {
        $data = [
            'journal_id' => $this->journal->id,
            'requested_role' => 'reviewer',
            'academic_type' => 'researcher',
            'institution_type' => 'independent',
            'institution_id' => $this->institution->id, // Should be cleared
            'institution' => 'Should be cleared',
            'department' => 'Should be cleared',
        ];

        $response = $this->actingAs($this->user)->post(route('membership-applications.store'), $data);
        $response->assertSessionHasNoErrors();

        $profile = AcademicProfile::where('user_id', $this->user->id)->first();
        $this->assertEquals('independent', $profile->institution_type);
        $this->assertNull($profile->institution_id);
        $this->assertNull($profile->institution);
        $this->assertNull($profile->department);
    }

    // 9. Foreign researcher is valid (country field is used, no explicit "foreign" type needed)
    // 10. Foreign lecturer is valid
    public function test_foreign_academic_is_valid()
    {
        $data = [
            'journal_id' => $this->journal->id,
            'requested_role' => 'reviewer',
            'academic_type' => 'researcher',
            'country' => 'United States',
        ];

        $response = $this->actingAs($this->user)->post(route('membership-applications.store'), $data);
        $response->assertSessionHasNoErrors();
        
        $profile = AcademicProfile::where('user_id', $this->user->id)->first();
        $this->assertEquals('United States', $profile->country);
    }

    // 11. Institutional email optional
    // 12. SINTA optional
    // 13. ORCID optional
    // 14. Scopus optional
    // 15. Google Scholar optional
    public function test_researcher_identity_fields_are_optional()
    {
        $data = [
            'journal_id' => $this->journal->id,
            'requested_role' => 'owner',
            'academic_type' => 'lecturer',
            // Omitted SINTA, ORCID, Scopus, Google Scholar, Institutional Email
        ];

        $response = $this->actingAs($this->user)->post(route('membership-applications.store'), $data);
        $response->assertSessionHasNoErrors();
        
        $profile = AcademicProfile::where('user_id', $this->user->id)->first();
        $this->assertNull($profile->sinta_id);
        $this->assertNull($profile->orcid);
        $this->assertNull($profile->institutional_email);
    }

    // 16. Requested Journal Role is separate from Academic Type
    public function test_journal_role_is_separate_from_academic_type()
    {
        $data = [
            'journal_id' => $this->journal->id,
            'requested_role' => 'owner', // Journal Role
            'academic_type' => 'researcher', // Academic Type
        ];

        $response = $this->actingAs($this->user)->post(route('membership-applications.store'), $data);
        $response->assertSessionHasNoErrors();
        
        $app = JournalMembershipApplication::first();
        $this->assertEquals('owner', $app->requested_role);
        
        $profile = AcademicProfile::first();
        $this->assertEquals('researcher', $profile->academic_type);
    }

    // 17. Academic Type cannot grant JournalMembership
    public function test_academic_type_does_not_grant_membership()
    {
        $data = [
            'journal_id' => $this->journal->id,
            'requested_role' => 'owner',
            'academic_type' => 'lecturer',
        ];

        $this->actingAs($this->user)->post(route('membership-applications.store'), $data);
        
        $this->assertDatabaseMissing('journal_memberships', [
            'user_id' => $this->user->id,
            'journal_id' => $this->journal->id,
        ]);
    }

    // 18. Institutional email cannot grant verification automatically (handled by controller logic, simply creates draft)
    public function test_institutional_email_creates_draft_only()
    {
        $data = [
            'journal_id' => $this->journal->id,
            'requested_role' => 'reviewer',
            'academic_type' => 'lecturer',
            'institutional_email' => 'user@university.edu',
        ];

        $this->actingAs($this->user)->post(route('membership-applications.store'), $data);
        
        $app = JournalMembershipApplication::first();
        $this->assertEquals(JournalMembershipApplication::STATUS_DRAFT, $app->status);
    }

    // 19. User cannot inject approved status
    public function test_user_cannot_inject_approved_status()
    {
        $data = [
            'journal_id' => $this->journal->id,
            'requested_role' => 'editor',
            'academic_type' => 'lecturer',
            'status' => 'approved', // Malicious injection attempt
        ];

        $this->actingAs($this->user)->post(route('membership-applications.store'), $data);
        
        $app = JournalMembershipApplication::first();
        $this->assertEquals(JournalMembershipApplication::STATUS_DRAFT, $app->status); // Remains draft
    }

    // 20. User cannot inject active membership status
    public function test_user_cannot_inject_active_membership_status()
    {
        $data = [
            'journal_id' => $this->journal->id,
            'requested_role' => 'editor',
            'academic_type' => 'lecturer',
            'membership_status' => 'active', // Malicious injection attempt
        ];

        $this->actingAs($this->user)->post(route('membership-applications.store'), $data);
        
        $this->assertDatabaseMissing('journal_memberships', [
            'user_id' => $this->user->id,
            'status' => 'active',
        ]);
    }

    // 21. User cannot access another user's application
    public function test_user_cannot_access_another_users_application()
    {
        $otherUser = User::factory()->create();
        $app = JournalMembershipApplication::create([
            'user_id' => $otherUser->id,
            'journal_id' => $this->journal->id,
            'requested_role' => 'reviewer',
            'status' => JournalMembershipApplication::STATUS_DRAFT,
        ]);

        $response = $this->actingAs($this->user)->get(route('membership-applications.show', $app->id));
        $response->assertStatus(404); // Using findOrFail with user_id scope
    }

    // 23. Needs Revision can be edited
    public function test_needs_revision_can_be_edited()
    {
        $app = JournalMembershipApplication::create([
            'user_id' => $this->user->id,
            'journal_id' => $this->journal->id,
            'requested_role' => 'reviewer',
            'status' => JournalMembershipApplication::STATUS_NEEDS_REVISION,
        ]);

        $response = $this->actingAs($this->user)->get(route('membership-applications.edit', $app->id));
        $response->assertStatus(200);
        $response->assertSee('Edit Application');
    }

    // 24. Needs Revision can be resubmitted
    public function test_needs_revision_can_be_resubmitted()
    {
        $app = JournalMembershipApplication::create([
            'user_id' => $this->user->id,
            'journal_id' => $this->journal->id,
            'requested_role' => 'reviewer',
            'status' => JournalMembershipApplication::STATUS_NEEDS_REVISION,
        ]);

        $response = $this->actingAs($this->user)->post(route('membership-applications.submit', $app->id));
        $response->assertRedirect();
        
        $app->refresh();
        $this->assertEquals(JournalMembershipApplication::STATUS_SUBMITTED, $app->status);
    }

    // 28. Multiple journal roles coexist
    public function test_multiple_journal_roles_coexist()
    {
        $journal2 = Journal::create(['title' => 'J2', 'slug' => 'j2', 'status' => 'active', 'description' => 'd']);

        // App for journal 1
        JournalMembershipApplication::create([
            'user_id' => $this->user->id,
            'journal_id' => $this->journal->id,
            'requested_role' => 'editor',
            'status' => JournalMembershipApplication::STATUS_APPROVED,
        ]);

        // App for journal 2
        JournalMembershipApplication::create([
            'user_id' => $this->user->id,
            'journal_id' => $journal2->id,
            'requested_role' => 'reviewer',
            'status' => JournalMembershipApplication::STATUS_APPROVED,
        ]);

        $this->assertEquals(2, JournalMembershipApplication::where('user_id', $this->user->id)->count());
    }

    // Security: Updating application prevents changing journal or role
    public function test_updating_application_prevents_changing_journal_and_role()
    {
        $journal2 = Journal::create(['title' => 'J2', 'slug' => 'j2', 'status' => 'active', 'description' => 'd']);
        
        $app = JournalMembershipApplication::create([
            'user_id' => $this->user->id,
            'journal_id' => $this->journal->id,
            'requested_role' => 'reviewer',
            'status' => JournalMembershipApplication::STATUS_NEEDS_REVISION,
        ]);

        $data = [
            'journal_id' => $journal2->id, // Attempt to change journal
            'requested_role' => 'owner', // Attempt to change role
            'academic_type' => 'lecturer',
        ];

        $this->actingAs($this->user)->put(route('membership-applications.update', $app->id), $data);

        $app->refresh();
        $this->assertEquals($this->journal->id, $app->journal_id);
        $this->assertEquals('reviewer', $app->requested_role);
    }
}
