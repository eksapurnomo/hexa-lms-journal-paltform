<?php

namespace Tests\Feature;

use App\Models\EditorialDecision;
use App\Models\Journal;
use App\Models\JournalMembership;
use App\Models\PeerReview;
use App\Models\ReviewAssignment;
use App\Models\ReviewCriterion;
use App\Models\ReviewRound;
use App\Models\Submission;
use App\Models\SubmissionFile;
use App\Models\SubmissionRevision;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Phase5FF4ReviewerUXTest extends TestCase
{
    use RefreshDatabase;

    // ────────────────────────────────────────────────
    // Helpers
    // ────────────────────────────────────────────────

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
        $sub = Submission::forceCreate([
            'created_by' => $creator->id,
            'journal_id' => $journal->id,
            'title'      => 'Test Submission',
            'abstract'   => 'Test abstract',
            'status'     => $status,
        ]);
        return $sub;
    }

    private function makeRevision(Submission $sub): SubmissionRevision
    {
        return SubmissionRevision::create(['submission_id' => $sub->id, 'version_number' => 1]);
    }

    private function makeRound(SubmissionRevision $rev): ReviewRound
    {
        return ReviewRound::create(['submission_revision_id' => $rev->id, 'round_number' => 1]);
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

    // ────────────────────────────────────────────────
    // 1. Reviewer can access own assignment
    // ────────────────────────────────────────────────
    public function test_reviewer_can_access_own_assignment()
    {
        $journal  = $this->makeJournal();
        $author   = $this->makeMember($journal, 'owner');
        $reviewer = $this->makeMember($journal, 'reviewer');
        $sub      = $this->makeSubmission($journal, $author);
        $rev      = $this->makeRevision($sub);
        $round    = $this->makeRound($rev);
        $assignment = $this->makeAssignment($round, $reviewer, $author);

        $response = $this->actingAs($reviewer)
            ->getJson("/api/reviewer/assignments/{$assignment->id}");

        $response->assertStatus(200);
        $response->assertJsonPath('data.id', $assignment->id);
    }

    // ────────────────────────────────────────────────
    // 2. Reviewer cannot access another reviewer's assignment
    // ────────────────────────────────────────────────
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

        // reviewerB tries to access reviewerA's assignment
        $response = $this->actingAs($reviewerB)
            ->getJson("/api/reviewer/assignments/{$assignmentA->id}");

        $response->assertStatus(403);
    }

    // ────────────────────────────────────────────────
    // 3. Unauthenticated user cannot access reviewer endpoint
    // ────────────────────────────────────────────────
    public function test_unauthenticated_cannot_access_reviewer_assignments()
    {
        $journal  = $this->makeJournal();
        $author   = $this->makeMember($journal, 'owner');
        $reviewer = $this->makeMember($journal, 'reviewer');
        $sub      = $this->makeSubmission($journal, $author);
        $rev      = $this->makeRevision($sub);
        $round    = $this->makeRound($rev);
        $assignment = $this->makeAssignment($round, $reviewer, $author);

        $response = $this->getJson("/api/reviewer/assignments/{$assignment->id}");
        $response->assertStatus(401);
    }

    // ────────────────────────────────────────────────
    // 4. Reviewer cannot access arbitrary submission
    //    (assignment list is scoped to reviewer_id)
    // ────────────────────────────────────────────────
    public function test_reviewer_list_only_shows_own_assignments()
    {
        $journal   = $this->makeJournal();
        $author    = $this->makeMember($journal, 'owner');
        $reviewerA = $this->makeMember($journal, 'reviewer');
        $reviewerB = $this->makeMember($journal, 'reviewer');
        $sub       = $this->makeSubmission($journal, $author);
        $rev       = $this->makeRevision($sub);
        $round     = $this->makeRound($rev);
        $this->makeAssignment($round, $reviewerA, $author);

        // reviewerB should see empty list — not reviewerA's assignment
        $response = $this->actingAs($reviewerB)
            ->getJson('/api/reviewer/assignments');

        $response->assertStatus(200);
        $response->assertJsonCount(0, 'data');
    }

    // ────────────────────────────────────────────────
    // 5. Blind/double-blind: author identity NOT exposed
    // ────────────────────────────────────────────────
    public function test_blind_review_does_not_expose_author_identity()
    {
        $journal  = $this->makeJournal();
        $author   = $this->makeMember($journal, 'owner');
        $reviewer = $this->makeMember($journal, 'reviewer');
        $sub      = $this->makeSubmission($journal, $author);
        $rev      = $this->makeRevision($sub);
        $round    = $this->makeRound($rev);
        $this->makeAssignment($round, $reviewer, $author, 'single_blind');

        $assignments = $this->actingAs($reviewer)
            ->getJson('/api/reviewer/assignments');
        $assignments->assertStatus(200);

        // submission.created_by and authors must NOT be present
        $submissionData = $assignments->json('data.0.submission');
        $this->assertArrayNotHasKey('created_by', $submissionData ?? []);
        $this->assertArrayNotHasKey('authors', $submissionData ?? []);
    }

    public function test_double_blind_review_does_not_expose_author_identity()
    {
        $journal  = $this->makeJournal();
        $author   = $this->makeMember($journal, 'owner');
        $reviewer = $this->makeMember($journal, 'reviewer');
        $sub      = $this->makeSubmission($journal, $author);
        $rev      = $this->makeRevision($sub);
        $round    = $this->makeRound($rev);
        $this->makeAssignment($round, $reviewer, $author, 'double_blind');

        $assignments = $this->actingAs($reviewer)
            ->getJson('/api/reviewer/assignments');
        $assignments->assertStatus(200);

        $submissionData = $assignments->json('data.0.submission');
        $this->assertArrayNotHasKey('created_by', $submissionData ?? []);
        $this->assertArrayNotHasKey('authors', $submissionData ?? []);
    }

    // ────────────────────────────────────────────────
    // 6. Reviewer can accept assignment
    // ────────────────────────────────────────────────
    public function test_reviewer_can_accept_assignment()
    {
        $journal  = $this->makeJournal();
        $author   = $this->makeMember($journal, 'owner');
        $reviewer = $this->makeMember($journal, 'reviewer');
        $sub      = $this->makeSubmission($journal, $author);
        $rev      = $this->makeRevision($sub);
        $round    = $this->makeRound($rev);
        $assignment = $this->makeAssignment($round, $reviewer, $author);

        $response = $this->actingAs($reviewer)
            ->postJson("/api/reviewer/assignments/{$assignment->id}/accept");

        $response->assertStatus(200);
        $this->assertDatabaseHas('review_assignments', [
            'id'     => $assignment->id,
            'status' => 'accepted',
        ]);
    }

    // ────────────────────────────────────────────────
    // 7. Reviewer can decline assignment
    // ────────────────────────────────────────────────
    public function test_reviewer_can_decline_assignment()
    {
        $journal  = $this->makeJournal();
        $author   = $this->makeMember($journal, 'owner');
        $reviewer = $this->makeMember($journal, 'reviewer');
        $sub      = $this->makeSubmission($journal, $author);
        $rev      = $this->makeRevision($sub);
        $round    = $this->makeRound($rev);
        $assignment = $this->makeAssignment($round, $reviewer, $author);

        $response = $this->actingAs($reviewer)
            ->postJson("/api/reviewer/assignments/{$assignment->id}/decline");

        $response->assertStatus(200);
        $this->assertDatabaseHas('review_assignments', [
            'id'     => $assignment->id,
            'status' => 'declined',
        ]);
    }

    // ────────────────────────────────────────────────
    // 8. Reviewer can submit valid review
    // ────────────────────────────────────────────────
    public function test_reviewer_can_submit_review()
    {
        $journal  = $this->makeJournal();
        $author   = $this->makeMember($journal, 'owner');
        $reviewer = $this->makeMember($journal, 'reviewer');
        $sub      = $this->makeSubmission($journal, $author);
        $rev      = $this->makeRevision($sub);
        $round    = $this->makeRound($rev);
        $assignment = $this->makeAssignment($round, $reviewer, $author);

        // Accept first
        $assignment->update(['status' => 'accepted', 'accepted_at' => now()]);

        $response = $this->actingAs($reviewer)
            ->postJson("/api/reviewer/assignments/{$assignment->id}/submit", [
                'recommendation'    => 'accept',
                'comments_to_editor' => 'Looks good.',
            ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('review_assignments', ['id' => $assignment->id, 'status' => 'submitted']);
        $this->assertDatabaseHas('peer_reviews', [
            'review_assignment_id' => $assignment->id,
            'recommendation'       => 'accept',
        ]);
    }

    // ────────────────────────────────────────────────
    // 9. Submitted PeerReview cannot be modified
    // ────────────────────────────────────────────────
    public function test_submitted_peer_review_cannot_be_modified()
    {
        $journal  = $this->makeJournal();
        $author   = $this->makeMember($journal, 'owner');
        $reviewer = $this->makeMember($journal, 'reviewer');
        $sub      = $this->makeSubmission($journal, $author);
        $rev      = $this->makeRevision($sub);
        $round    = $this->makeRound($rev);
        $assignment = $this->makeAssignment($round, $reviewer, $author);
        $assignment->update(['status' => 'accepted', 'accepted_at' => now()]);

        // Submit review
        $this->actingAs($reviewer)
            ->postJson("/api/reviewer/assignments/{$assignment->id}/submit", [
                'recommendation' => 'accept',
            ])->assertStatus(200);

        // Try to submit again
        $response = $this->actingAs($reviewer)
            ->postJson("/api/reviewer/assignments/{$assignment->id}/submit", [
                'recommendation' => 'reject',
            ]);

        // Must fail — assignment is now 'submitted', cannot re-submit
        $response->assertStatus(422);
    }

    // ────────────────────────────────────────────────
    // 10. Locked ReviewRound rejects review submission
    //     (round with EditorialDecision)
    // ────────────────────────────────────────────────
    public function test_locked_round_rejects_new_assignment()
    {
        $journal  = $this->makeJournal();
        $author   = $this->makeMember($journal, 'owner');
        $reviewer = $this->makeMember($journal, 'reviewer');
        $owner    = $author;
        $sub      = $this->makeSubmission($journal, $author);
        $rev      = $this->makeRevision($sub);
        $round    = $this->makeRound($rev);

        // Record editorial decision — locking the round
        EditorialDecision::create([
            'review_round_id' => $round->id,
            'user_id'         => $owner->id,
            'decision'        => 'accept',
        ]);

        // Attempt to create new assignment on locked round — must throw at model level
        $this->expectException(\Exception::class);
        ReviewAssignment::create([
            'review_round_id' => $round->id,
            'reviewer_id'     => $reviewer->id,
            'assigned_by'     => $owner->id,
            'status'          => 'assigned',
            'review_mode'     => 'single_blind',
            'assigned_at'     => now(),
        ]);
    }

    // ────────────────────────────────────────────────
    // 11. Reviewer can access only the assigned revision files
    //     (file belongs to revision attached to their round)
    // ────────────────────────────────────────────────
    public function test_reviewer_can_access_assigned_revision_files()
    {
        $journal  = $this->makeJournal();
        $author   = $this->makeMember($journal, 'owner');
        $reviewer = $this->makeMember($journal, 'reviewer');
        $sub      = $this->makeSubmission($journal, $author);
        $rev      = $this->makeRevision($sub);

        // Create two revisions — reviewer should only see rev1's files
        $rev2  = SubmissionRevision::create(['submission_id' => $sub->id, 'version_number' => 2]);
        $round = $this->makeRound($rev); // round on rev1
        $assignment = $this->makeAssignment($round, $reviewer, $author);
        $assignment->update(['status' => 'accepted', 'accepted_at' => now()]);

        // File attached to rev2 (different revision)
        $file2 = SubmissionFile::create([
            'submission_revision_id' => $rev2->id,
            'original_name' => 'rev2.pdf',
            'file_path'     => 'fake/rev2.pdf',
            'size'          => 100,
            'disk'          => 'local',
            'mime_type'     => 'application/pdf',
        ]);

        // Reviewer tries to download a file from rev2 via their assignment (which belongs to rev1)
        $response = $this->actingAs($reviewer)
            ->get("/api/reviewer/assignments/{$assignment->id}/files/{$file2->id}/download");

        $response->assertStatus(403);
    }

    // ────────────────────────────────────────────────
    // 12. Criterion responses are immutable after submission
    // ────────────────────────────────────────────────
    public function test_criterion_responses_immutable_after_submission()
    {
        $journal   = $this->makeJournal();
        $author    = $this->makeMember($journal, 'owner');
        $reviewer  = $this->makeMember($journal, 'reviewer');
        $sub       = $this->makeSubmission($journal, $author);
        $rev       = $this->makeRevision($sub);
        $round     = $this->makeRound($rev);
        $assignment = $this->makeAssignment($round, $reviewer, $author);
        $assignment->update(['status' => 'accepted', 'accepted_at' => now()]);

        $criterion = ReviewCriterion::create([
            'journal_id' => $journal->id,
            'name'       => 'Originality',
            'type'       => 'rating',
            'config'     => ['min' => 1, 'max' => 5],
            'status'     => 'active',
        ]);

        // Submit with criterion response
        $this->actingAs($reviewer)
            ->postJson("/api/reviewer/assignments/{$assignment->id}/submit", [
                'recommendation'    => 'accept',
                'criteria_responses' => [
                    ['criterion_id' => $criterion->id, 'response' => '4'],
                ],
            ])->assertStatus(200);

        // Verify criterion response was persisted
        $this->assertDatabaseHas('peer_reviews', [
            'review_assignment_id' => $assignment->id,
            'recommendation'       => 'accept',
        ]);

        // Try to modify the criterion response directly — must throw
        $peerReview = PeerReview::where('review_assignment_id', $assignment->id)->first();
        $this->assertNotNull($peerReview->submitted_at);

        $criterionResponse = $peerReview->criterionResponses()->first();
        $this->assertNotNull($criterionResponse);

        $this->expectException(\Exception::class);
        $criterionResponse->update(['response' => '1']); // Must throw
    }

    // ────────────────────────────────────────────────
    // 13. Criteria endpoint is scoped to assignment's journal
    // ────────────────────────────────────────────────
    public function test_criteria_endpoint_returns_journal_criteria()
    {
        $journal  = $this->makeJournal();
        $author   = $this->makeMember($journal, 'owner');
        $reviewer = $this->makeMember($journal, 'reviewer');
        $sub      = $this->makeSubmission($journal, $author);
        $rev      = $this->makeRevision($sub);
        $round    = $this->makeRound($rev);
        $assignment = $this->makeAssignment($round, $reviewer, $author);

        ReviewCriterion::create([
            'journal_id' => $journal->id,
            'name'       => 'Quality',
            'type'       => 'rating',
            'config'     => ['min' => 1, 'max' => 5],
            'status'     => 'active',
        ]);

        $response = $this->actingAs($reviewer)
            ->getJson("/api/reviewer/assignments/{$assignment->id}/criteria");

        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data');
        $response->assertJsonPath('data.0.name', 'Quality');
    }

    // ────────────────────────────────────────────────
    // 14. Reviewer B cannot use Reviewer A's assignment ID
    //     to access criteria (authorization check on criteria endpoint)
    // ────────────────────────────────────────────────
    public function test_reviewer_cannot_access_criteria_via_another_assignment()
    {
        $journal   = $this->makeJournal();
        $author    = $this->makeMember($journal, 'owner');
        $reviewerA = $this->makeMember($journal, 'reviewer');
        $reviewerB = $this->makeMember($journal, 'reviewer');
        $sub       = $this->makeSubmission($journal, $author);
        $rev       = $this->makeRevision($sub);
        $round     = $this->makeRound($rev);
        $assignmentA = $this->makeAssignment($round, $reviewerA, $author);

        $response = $this->actingAs($reviewerB)
            ->getJson("/api/reviewer/assignments/{$assignmentA->id}/criteria");

        $response->assertStatus(403);
    }
}
