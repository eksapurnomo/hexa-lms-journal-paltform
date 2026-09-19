<?php

namespace Tests\Feature;

use App\Models\Journal;
use App\Models\JournalMembership;
use App\Models\ReviewAssignment;
use App\Models\ReviewCriterion;
use App\Models\ReviewRound;
use App\Models\Submission;
use App\Models\SubmissionRevision;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Phase5GReviewSubmissionTest extends TestCase
{
    use RefreshDatabase;

    private function makeJournal(): Journal
    {
        return Journal::create([
            'title'  => 'Test Journal ' . uniqid(),
            'slug'   => \Illuminate\Support\Str::uuid(),
            'status' => 'active',
        ]);
    }

    private function makeMember(Journal $journal, string $role): User
    {
        $user = User::factory()->create();
        JournalMembership::create([
            'journal_id' => $journal->id,
            'user_id'    => $user->id,
            'role'       => $role,
            'status'     => 'active',
        ]);
        return $user;
    }

    private function makeSubmission(Journal $journal, User $creator, string $status = 'review_pending'): Submission
    {
        return Submission::forceCreate([
            'created_by' => $creator->id,
            'journal_id' => $journal->id,
            'title'      => 'Test Submission',
            'abstract'   => 'Test abstract',
            'status'     => $status,
        ]);
    }

    private function makeRevision(Submission $sub): SubmissionRevision
    {
        return SubmissionRevision::create(['submission_id' => $sub->id, 'version_number' => 1]);
    }

    private function makeRound(SubmissionRevision $rev): ReviewRound
    {
        return ReviewRound::create([
            'submission_revision_id' => $rev->id, 
            'round_number' => 1,
            'minimum_reviewers' => 1,
            'target_reviewers' => 2,
            'maximum_reviewers' => 3,
            'review_model' => 'single_blind',
        ]);
    }

    private function makeAssignment(ReviewRound $round, User $reviewer, User $assigner, string $mode = 'single_blind'): ReviewAssignment
    {
        return ReviewAssignment::create([
            'review_round_id' => $round->id,
            'reviewer_id'     => $reviewer->id,
            'assigned_by'     => $assigner->id,
            'status'          => 'assigned',
            'review_mode'     => $mode,
            'assigned_at'     => now(),
        ]);
    }

    // Test A — Reviewer can open own assignment
    public function test_reviewer_can_open_own_assignment()
    {
        $journal  = $this->makeJournal();
        $author   = $this->makeMember($journal, 'owner');
        $reviewer = $this->makeMember($journal, 'reviewer');
        $sub      = $this->makeSubmission($journal, $author);
        $rev      = $this->makeRevision($sub);
        $round    = $this->makeRound($rev);
        $assignment = $this->makeAssignment($round, $reviewer, $author);

        $response = $this->actingAs($reviewer)->getJson("/api/reviewer/assignments/{$assignment->id}");
        $response->assertStatus(200);
        $response->assertJsonPath('data.id', $assignment->id);
    }

    // Test B — Reviewer cannot access another reviewer's assignment
    public function test_reviewer_cannot_access_another_reviewers_assignment()
    {
        $journal   = $this->makeJournal();
        $author    = $this->makeMember($journal, 'owner');
        $reviewerA = $this->makeMember($journal, 'reviewer');
        $reviewerB = $this->makeMember($journal, 'reviewer');
        $sub       = $this->makeSubmission($journal, $author);
        $rev       = $this->makeRevision($sub);
        $round     = $this->makeRound($rev);
        $assignmentA = $this->makeAssignment($round, $reviewerA, $author);

        $response = $this->actingAs($reviewerB)->getJson("/api/reviewer/assignments/{$assignmentA->id}");
        $response->assertStatus(403);
    }

    // Test C — Reviewer cannot submit another reviewer's review
    public function test_reviewer_cannot_submit_another_reviewers_review()
    {
        $journal   = $this->makeJournal();
        $author    = $this->makeMember($journal, 'owner');
        $reviewerA = $this->makeMember($journal, 'reviewer');
        $reviewerB = $this->makeMember($journal, 'reviewer');
        $sub       = $this->makeSubmission($journal, $author);
        $rev       = $this->makeRevision($sub);
        $round     = $this->makeRound($rev);
        $assignmentA = $this->makeAssignment($round, $reviewerA, $author);
        $assignmentA->update(['status' => 'accepted', 'accepted_at' => now()]);

        $response = $this->actingAs($reviewerB)->postJson("/api/reviewer/assignments/{$assignmentA->id}/submit", [
            'recommendation' => 'accept'
        ]);
        $response->assertStatus(403);
    }

    // Test D — Double-blind protection
    public function test_double_blind_protection()
    {
        $journal  = $this->makeJournal();
        $author   = $this->makeMember($journal, 'owner');
        $reviewer = $this->makeMember($journal, 'reviewer');
        $sub      = $this->makeSubmission($journal, $author);
        
        // Add author to submission authors
        $sub->authors()->forceCreate([
            'submission_id' => $sub->id,
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'sequence' => 1,
            'is_corresponding' => true,
        ]);

        $rev      = $this->makeRevision($sub);
        
        $round = clone $this->makeRound($rev);
        $round->update(['review_model' => 'double_blind']);
        
        $assignment = $this->makeAssignment($round, $reviewer, $author, 'double_blind');

        $response = $this->actingAs($reviewer)->getJson("/api/reviewer/assignments/{$assignment->id}");
        $response->assertStatus(200);

        $json = $response->json();
        
        $this->assertArrayNotHasKey('authors', $json['data']['submission']);
        $this->assertArrayNotHasKey('created_by', $json['data']['submission']);
        $this->assertStringNotContainsString('John', json_encode($json));
    }

    // Test E — Recommendation required
    public function test_recommendation_required()
    {
        $journal  = $this->makeJournal();
        $author   = $this->makeMember($journal, 'owner');
        $reviewer = $this->makeMember($journal, 'reviewer');
        $sub      = $this->makeSubmission($journal, $author);
        $rev      = $this->makeRevision($sub);
        $round    = $this->makeRound($rev);
        $assignment = $this->makeAssignment($round, $reviewer, $author);
        $assignment->update(['status' => 'accepted', 'accepted_at' => now()]);

        $response = $this->actingAs($reviewer)->postJson("/api/reviewer/assignments/{$assignment->id}/submit", [
            'comments_to_editor' => 'Looks good.'
        ]);
        
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['recommendation']);
    }

    // Test F — Invalid recommendation rejected
    public function test_invalid_recommendation_rejected()
    {
        $journal  = $this->makeJournal();
        $author   = $this->makeMember($journal, 'owner');
        $reviewer = $this->makeMember($journal, 'reviewer');
        $sub      = $this->makeSubmission($journal, $author);
        $rev      = $this->makeRevision($sub);
        $round    = $this->makeRound($rev);
        $assignment = $this->makeAssignment($round, $reviewer, $author);
        $assignment->update(['status' => 'accepted', 'accepted_at' => now()]);

        $response = $this->actingAs($reviewer)->postJson("/api/reviewer/assignments/{$assignment->id}/submit", [
            'recommendation' => 'awesome_paper'
        ]);
        
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['recommendation']);
    }

    // Test G — Successful review submission
    public function test_successful_review_submission()
    {
        $journal  = $this->makeJournal();
        $author   = $this->makeMember($journal, 'owner');
        $reviewer = $this->makeMember($journal, 'reviewer');
        $sub      = $this->makeSubmission($journal, $author);
        $rev      = $this->makeRevision($sub);
        $round    = $this->makeRound($rev);
        $assignment = $this->makeAssignment($round, $reviewer, $author);
        $assignment->update(['status' => 'accepted', 'accepted_at' => now()]);

        $response = $this->actingAs($reviewer)->postJson("/api/reviewer/assignments/{$assignment->id}/submit", [
            'recommendation' => 'minor_revision',
            'comments_to_editor' => 'Looks great.',
            'comments_to_author' => 'Please fix typos.',
        ]);
        
        $response->assertStatus(200);
        $this->assertDatabaseHas('peer_reviews', [
            'review_assignment_id' => $assignment->id,
            'recommendation' => 'minor_revision',
            'comments_to_editor' => 'Looks great.',
            'comments_to_author' => 'Please fix typos.',
        ]);
        $this->assertDatabaseHas('review_assignments', [
            'id' => $assignment->id,
            'status' => 'submitted',
        ]);
    }

    // Test H — Completed review is immutable
    public function test_completed_review_is_immutable()
    {
        $journal  = $this->makeJournal();
        $author   = $this->makeMember($journal, 'owner');
        $reviewer = $this->makeMember($journal, 'reviewer');
        $sub      = $this->makeSubmission($journal, $author);
        $rev      = $this->makeRevision($sub);
        $round    = $this->makeRound($rev);
        $assignment = $this->makeAssignment($round, $reviewer, $author);
        $assignment->update(['status' => 'accepted', 'accepted_at' => now()]);

        $this->actingAs($reviewer)->postJson("/api/reviewer/assignments/{$assignment->id}/submit", [
            'recommendation' => 'minor_revision'
        ]);
        
        // Attempt to submit again
        $response = $this->actingAs($reviewer)->postJson("/api/reviewer/assignments/{$assignment->id}/submit", [
            'recommendation' => 'accept'
        ]);
        $response->assertStatus(422);
    }

    // Test I — Assignment is not completion
    public function test_assignment_is_not_completion()
    {
        $journal  = $this->makeJournal();
        $author   = $this->makeMember($journal, 'owner');
        $reviewer = $this->makeMember($journal, 'reviewer');
        $sub      = $this->makeSubmission($journal, $author);
        $rev      = $this->makeRevision($sub);
        $round    = $this->makeRound($rev);
        $assignment = $this->makeAssignment($round, $reviewer, $author);

        $this->assertEquals(1, $round->assignments()->count());
        $this->assertEquals(0, $round->assignments()->where('status', 'submitted')->count());
        
        // Ensure PeerReview table is empty for this assignment
        $this->assertDatabaseMissing('peer_reviews', [
            'review_assignment_id' => $assignment->id
        ]);
    }

    // Test J — Completed review count
    public function test_completed_review_count()
    {
        $journal  = $this->makeJournal();
        $author   = $this->makeMember($journal, 'owner');
        $reviewer = $this->makeMember($journal, 'reviewer');
        $sub      = $this->makeSubmission($journal, $author);
        $rev      = $this->makeRevision($sub);
        $round    = $this->makeRound($rev);
        $assignment = $this->makeAssignment($round, $reviewer, $author);
        $assignment->update(['status' => 'accepted', 'accepted_at' => now()]);

        $this->actingAs($reviewer)->postJson("/api/reviewer/assignments/{$assignment->id}/submit", [
            'recommendation' => 'minor_revision'
        ]);

        $this->assertEquals(1, $round->assignments()->count());
        $this->assertEquals(1, $round->assignments()->where('status', 'submitted')->count());
    }
}
