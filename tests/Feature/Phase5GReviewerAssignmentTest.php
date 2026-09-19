<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Journal;
use App\Models\Submission;
use App\Models\SubmissionRevision;
use App\Models\ReviewRound;
use App\Models\ReviewAssignment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Phase5GReviewerAssignmentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
    }

    private function setupTestEnvironment()
    {
        // For User, we might need a password and email
        $createUser = function($email, $isAdmin = false) {
            return User::create([
                'name' => 'Test User',
                'email' => $email,
                'password' => bcrypt('password'),
                'is_admin' => $isAdmin
            ]);
        };

        $admin = $createUser('admin@test.com', true);
        $journal = Journal::create(['title' => 'Test Journal', 'slug' => 'test-journal', 'status' => 'active']);
        
        $owner = $createUser('owner@test.com');
        $journal->memberships()->create(['user_id' => $owner->id, 'role' => 'owner', 'status' => 'active']);

        $assignedEditor = $createUser('editor1@test.com');
        $journal->memberships()->create(['user_id' => $assignedEditor->id, 'role' => 'editor', 'status' => 'active']);

        $unassignedEditor = $createUser('editor2@test.com');
        $journal->memberships()->create(['user_id' => $unassignedEditor->id, 'role' => 'editor', 'status' => 'active']);

        $reviewer1 = $createUser('reviewer1@test.com');
        $m1 = $journal->memberships()->create(['user_id' => $reviewer1->id, 'role' => 'reviewer', 'status' => 'active']);
        \App\Models\JournalReviewerCapability::create(['journal_membership_id' => $m1->id, 'available_for_review' => true]);

        $reviewer2 = $createUser('reviewer2@test.com');
        $m2 = $journal->memberships()->create(['user_id' => $reviewer2->id, 'role' => 'reviewer', 'status' => 'active']);
        \App\Models\JournalReviewerCapability::create(['journal_membership_id' => $m2->id, 'available_for_review' => true]);

        $reviewer3 = $createUser('reviewer3@test.com');
        $m3 = $journal->memberships()->create(['user_id' => $reviewer3->id, 'role' => 'reviewer', 'status' => 'active']);
        \App\Models\JournalReviewerCapability::create(['journal_membership_id' => $m3->id, 'available_for_review' => true]);

        $author = $createUser('author@test.com');

        $submission = Submission::forceCreate([
            'title' => 'Test Submission',
            'abstract' => 'Test abstract',
            'journal_id' => $journal->id,
            'editor_id' => $assignedEditor->id,
            'status' => 'review_pending',
        ]);
        $submission->authors()->create(['first_name' => 'Test', 'last_name' => 'Author', 'email' => 'author@test.com', 'user_id' => $author->id, 'is_corresponding' => true, 'sequence' => 1]);

        $revision = SubmissionRevision::forceCreate([
            'submission_id' => $submission->id,
            'version_number' => 1,
        ]);

        $round = ReviewRound::forceCreate([
            'submission_revision_id' => $revision->id,
            'round_number' => 1,
            'review_model' => 'double_blind',
            'minimum_reviewers' => 2,
            'target_reviewers' => 2,
            'maximum_reviewers' => 2,
        ]);

        return compact('admin', 'owner', 'assignedEditor', 'unassignedEditor', 'reviewer1', 'reviewer2', 'reviewer3', 'author', 'submission', 'round', 'journal');
    }

    public function test_assignment_authorization()
    {
        $env = $this->setupTestEnvironment();
        extract($env);

        $assignPayload = [
            'reviewer_id' => $reviewer1->id,
            'review_mode' => 'double_blind'
        ];

        // Author -> forbidden
        $this->actingAs($author)
             ->postJson("/api/editorial/submissions/{$submission->id}/review-assignments", $assignPayload)
             ->assertStatus(403);

        // Reviewer -> forbidden
        $this->actingAs($reviewer1)
             ->postJson("/api/editorial/submissions/{$submission->id}/review-assignments", $assignPayload)
             ->assertStatus(403);

        // Unassigned Editor -> forbidden
        $this->actingAs($unassignedEditor)
             ->postJson("/api/editorial/submissions/{$submission->id}/review-assignments", $assignPayload)
             ->assertStatus(403);

        // Admin -> allowed
        $this->actingAs($admin)
             ->postJson("/api/editorial/submissions/{$submission->id}/review-assignments", $assignPayload)
             ->assertSuccessful();

        $this->assertDatabaseHas('review_assignments', [
            'review_round_id' => $round->id,
            'reviewer_id' => $reviewer1->id,
        ]);
    }

    public function test_duplicate_assignment_blocked()
    {
        $env = $this->setupTestEnvironment();
        extract($env);

        $assignPayload = [
            'reviewer_id' => $reviewer1->id,
            'review_mode' => 'double_blind'
        ];

        $this->actingAs($assignedEditor)
             ->postJson("/api/editorial/submissions/{$submission->id}/review-assignments", $assignPayload)
             ->assertSuccessful();

        // Try duplicate
        $response = $this->actingAs($assignedEditor)
             ->postJson("/api/editorial/submissions/{$submission->id}/review-assignments", $assignPayload);
             
        $response->assertStatus(500); // Because we throw \Exception, typically caught by Laravel as 500 in tests, or we check the message
    }

    public function test_maximum_reviewer_limit()
    {
        $env = $this->setupTestEnvironment();
        extract($env);
        // Round has maximum_reviewers = 2

        $this->actingAs($assignedEditor)
             ->postJson("/api/editorial/submissions/{$submission->id}/review-assignments", ['reviewer_id' => $reviewer1->id])
             ->assertSuccessful();

        $this->actingAs($assignedEditor)
             ->postJson("/api/editorial/submissions/{$submission->id}/review-assignments", ['reviewer_id' => $reviewer2->id])
             ->assertSuccessful();

        // Try to assign a 3rd reviewer
        $response = $this->actingAs($assignedEditor)
             ->postJson("/api/editorial/submissions/{$submission->id}/review-assignments", ['reviewer_id' => $reviewer3->id]);
             
        $response->assertStatus(500);
    }

    public function test_reviewer_workspace_isolation()
    {
        $env = $this->setupTestEnvironment();
        extract($env);

        // Assign reviewer 1
        $this->actingAs($assignedEditor)
             ->postJson("/api/editorial/submissions/{$submission->id}/review-assignments", ['reviewer_id' => $reviewer1->id])
             ->assertSuccessful();
             
        $assignment = ReviewAssignment::first();

        // Reviewer 1 can access their assignment
        $this->actingAs($reviewer1)
             ->getJson("/api/reviewer/assignments/{$assignment->id}")
             ->assertSuccessful();

        // Reviewer 2 cannot access Reviewer 1's assignment
        $this->actingAs($reviewer2)
             ->getJson("/api/reviewer/assignments/{$assignment->id}")
             ->assertStatus(403);

        // Unassigned reviewer sees empty list
        $this->actingAs($reviewer3)
             ->getJson("/api/reviewer/assignments")
             ->assertSuccessful()
             ->assertJsonCount(0, 'data');
    }

    public function test_double_blind_author_identity_protection()
    {
        $env = $this->setupTestEnvironment();
        extract($env);

        $this->actingAs($assignedEditor)
             ->postJson("/api/editorial/submissions/{$submission->id}/review-assignments", ['reviewer_id' => $reviewer1->id, 'review_mode' => 'double_blind'])
             ->assertSuccessful();
             
        $assignment = ReviewAssignment::first();

        // Fetch assignment detail as reviewer
        $response = $this->actingAs($reviewer1)
             ->getJson("/api/reviewer/assignments/{$assignment->id}")
             ->assertSuccessful();
             
        $json = $response->json();
        
        // Ensure authors are NOT included in the submission object
        $this->assertArrayNotHasKey('authors', $json['data']['submission']);
        
        // Ensure the author name is nowhere in the JSON response
        $this->assertStringNotContainsString('Test Author', json_encode($json));
    }
}
