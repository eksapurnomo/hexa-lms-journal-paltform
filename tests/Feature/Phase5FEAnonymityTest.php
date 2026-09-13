<?php

namespace Tests\Feature;

use App\Models\Journal;
use App\Models\JournalMembership;
use App\Models\ReviewAssignment;
use App\Models\ReviewRound;
use App\Models\Submission;
use App\Models\SubmissionRevision;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Phase5FEAnonymityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        \Illuminate\Support\Facades\Schema::disableForeignKeyConstraints();
    }

    public function test_reviewer_assignment_resource_includes_identity_in_single_blind_mode()
    {
        $journal = Journal::forceCreate(['title' => 'Test', 'slug' => 'test', 'description' => 'Test', 'status' => 'active', 'created_by' => 1]);
        $author = User::forceCreate(['name' => 'Author', 'email' => 'author@example.com', 'password' => 'secret']);
        $reviewer = User::forceCreate(['name' => 'Reviewer', 'email' => 'reviewer@example.com', 'password' => 'secret']);

        JournalMembership::forceCreate([
            'journal_id' => $journal->id,
            'user_id' => $reviewer->id,
            'role' => 'reviewer',
            'status' => 'active'
        ]);

        $submission = Submission::forceCreate([
            'journal_id' => $journal->id,
            'created_by' => $author->id,
            'title' => 'Test',
            'status' => 'review_pending'
        ]);

        $revision = SubmissionRevision::forceCreate(['submission_id' => $submission->id, 'version_number' => 1]);
        $round = ReviewRound::forceCreate(['submission_revision_id' => $revision->id, 'round_number' => 1, 'review_model' => 'single_blind']);
        $assignment = ReviewAssignment::forceCreate([
            'review_round_id' => $round->id,
            'reviewer_id' => $reviewer->id,
            'assigned_by' => 1,
            'review_mode' => 'single_blind'
        ]);

        $response = $this->actingAs($reviewer)->getJson("/api/reviewer/assignments/{$assignment->id}");

        $response->assertStatus(200);
        $data = $response->json('data');

        $this->assertEquals('single_blind', $data['review_mode']);
        $this->assertNotNull($data['submission']);
        $this->assertArrayHasKey('created_by', $data['submission'], 'Author identity MUST be included in single blind mode');
        $this->assertArrayHasKey('authors', $data['submission'], 'Authors relationship MUST be included in single blind mode');
    }

    public function test_reviewer_assignment_resource_strips_identity_in_double_blind_mode()
    {
        $journal = Journal::forceCreate(['title' => 'Test', 'slug' => 'test2', 'description' => 'Test', 'status' => 'active', 'created_by' => 1]);
        $author = User::forceCreate(['name' => 'Author2', 'email' => 'author2@example.com', 'password' => 'secret']);
        $reviewer = User::forceCreate(['name' => 'Reviewer2', 'email' => 'reviewer2@example.com', 'password' => 'secret']);

        JournalMembership::forceCreate([
            'journal_id' => $journal->id,
            'user_id' => $reviewer->id,
            'role' => 'reviewer',
            'status' => 'active'
        ]);

        $submission = Submission::forceCreate([
            'journal_id' => $journal->id,
            'created_by' => $author->id,
            'title' => 'Test',
            'status' => 'review_pending'
        ]);

        $revision = SubmissionRevision::forceCreate(['submission_id' => $submission->id, 'version_number' => 1]);
        $round = ReviewRound::forceCreate(['submission_revision_id' => $revision->id, 'round_number' => 1, 'review_model' => 'double_blind']);
        $assignment = ReviewAssignment::forceCreate([
            'review_round_id' => $round->id,
            'reviewer_id' => $reviewer->id,
            'assigned_by' => 1,
            'review_mode' => 'double_blind'
        ]);

        $response = $this->actingAs($reviewer)->getJson("/api/reviewer/assignments/{$assignment->id}");

        $response->assertStatus(200);
        $data = $response->json('data');

        $this->assertEquals('double_blind', $data['review_mode']);
        $this->assertArrayNotHasKey('created_by', $data['submission']);
        $this->assertArrayNotHasKey('authors', $data['submission']);
    }

    public function test_reviewer_assignment_resource_includes_identity_in_open_mode()
    {
        $journal = Journal::forceCreate(['title' => 'Test', 'slug' => 'test3', 'description' => 'Test', 'status' => 'active', 'created_by' => 1]);
        $author = User::forceCreate(['name' => 'Author3', 'email' => 'author3@example.com', 'password' => 'secret']);
        $reviewer = User::forceCreate(['name' => 'Reviewer3', 'email' => 'reviewer3@example.com', 'password' => 'secret']);

        JournalMembership::forceCreate([
            'journal_id' => $journal->id,
            'user_id' => $reviewer->id,
            'role' => 'reviewer',
            'status' => 'active'
        ]);

        $submission = Submission::forceCreate([
            'journal_id' => $journal->id,
            'created_by' => $author->id,
            'title' => 'Test',
            'status' => 'review_pending'
        ]);

        $revision = SubmissionRevision::forceCreate(['submission_id' => $submission->id, 'version_number' => 1]);
        $round = ReviewRound::forceCreate(['submission_revision_id' => $revision->id, 'round_number' => 1, 'review_model' => 'open']);
        $assignment = ReviewAssignment::forceCreate([
            'review_round_id' => $round->id,
            'reviewer_id' => $reviewer->id,
            'assigned_by' => 1,
            'review_mode' => 'open'
        ]);

        $response = $this->actingAs($reviewer)->getJson("/api/reviewer/assignments/{$assignment->id}");

        $response->assertStatus(200);
        $data = $response->json('data');

        $this->assertEquals('open', $data['review_mode']);
        $this->assertArrayHasKey('created_by', $data['submission'], 'Author identity MUST be included in open mode');
        $this->assertEquals($author->id, $data['submission']['created_by']);
    }
}
