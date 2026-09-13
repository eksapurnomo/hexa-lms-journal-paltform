<?php

namespace Tests\Feature;

use App\Models\EditorialDecision;
use App\Models\Journal;
use App\Models\JournalMembership;
use App\Models\PeerReview;
use App\Models\ReviewAssignment;
use App\Models\ReviewCriterion;
use App\Models\ReviewCriterionResponse;
use App\Models\ReviewRound;
use App\Models\Submission;
use App\Models\SubmissionFile;
use App\Models\SubmissionRevision;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Phase 5F-F.5 Integration Tests
 * 
 * End-to-end security/domain integration scenarios verifying
 * that all phases work coherently together.
 */
class Phase5FF5IntegrationTest extends TestCase
{
    use RefreshDatabase;

    // ─────────────────────────────────────────────────
    // Shared helpers
    // ─────────────────────────────────────────────────

    private function makeJournal(): Journal
    {
        return Journal::create([
            'title'  => 'Integration Journal ' . uniqid(),
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

    private function makeSubmission(Journal $journal, User $creator, string $status = 'submitted'): Submission
    {
        return Submission::forceCreate([
            'created_by' => $creator->id,
            'journal_id' => $journal->id,
            'title'      => 'Integration Test Submission',
            'abstract'   => 'Abstract text',
            'status'     => $status,
        ]);
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

    // ─────────────────────────────────────────────────
    // Scenario 1: Author isolation
    // ─────────────────────────────────────────────────
    public function test_scenario_1_author_cannot_access_another_authors_submission()
    {
        $journal  = $this->makeJournal();
        $authorA  = $this->makeMember($journal, 'owner');
        $authorB  = User::factory()->create();

        $submissionA = $this->makeSubmission($journal, $authorA, 'draft');

        // Author B tries to GET Author A's submission
        $response = $this->actingAs($authorB, 'api')
            ->getJson("/api/submissions/{$submissionA->id}");

        $response->assertStatus(403);
    }

    // ─────────────────────────────────────────────────
    // Scenario 2: Editorial visibility — correct hierarchy
    // ─────────────────────────────────────────────────
    public function test_scenario_2_editorial_user_sees_correct_hierarchy()
    {
        $journal  = $this->makeJournal();
        $owner    = $this->makeMember($journal, 'owner');
        $author   = User::factory()->create();
        $reviewer = $this->makeMember($journal, 'reviewer');

        $sub      = $this->makeSubmission($journal, $author, 'review_pending');
        $rev      = $this->makeRevision($sub);
        $round    = $this->makeRound($rev);
        $assign   = $this->makeAssignment($round, $reviewer, $owner);

        $response = $this->actingAs($owner)
            ->getJson("/api/editorial/submissions/{$sub->id}");

        $response->assertStatus(200);

        // Revision is nested under submission
        $revisions = $response->json('data.revisions');
        $this->assertNotEmpty($revisions, 'Revisions must be present');
        $this->assertEquals($rev->id, $revisions[0]['id']);

        // ReviewRound nested under revision
        $rounds = $revisions[0]['review_rounds'];
        $this->assertNotEmpty($rounds, 'ReviewRounds must be present under revision');
        $this->assertEquals($round->id, $rounds[0]['id']);

        // Assignment nested under round
        $assignments = $rounds[0]['assignments'];
        $this->assertNotEmpty($assignments, 'Assignments must be present under round');
        $this->assertEquals($assign->id, $assignments[0]['id']);

        // Reviewer identity SHOULD be visible to editor
        $this->assertEquals($reviewer->id, $assignments[0]['reviewer_id']);
    }

    // ─────────────────────────────────────────────────
    // Scenario 3: Reviewer isolation
    // ─────────────────────────────────────────────────
    public function test_scenario_3_reviewer_a_cannot_access_reviewer_b_assignment()
    {
        $journal   = $this->makeJournal();
        $owner     = $this->makeMember($journal, 'owner');
        $reviewerA = $this->makeMember($journal, 'reviewer');
        $reviewerB = $this->makeMember($journal, 'reviewer');
        $sub       = $this->makeSubmission($journal, $owner, 'review_pending');
        $rev       = $this->makeRevision($sub);
        $round     = $this->makeRound($rev);
        $assignA   = $this->makeAssignment($round, $reviewerA, $owner);

        // Reviewer B tries to access Reviewer A's assignment detail
        $response = $this->actingAs($reviewerB)
            ->getJson("/api/reviewer/assignments/{$assignA->id}");
        $response->assertStatus(403);

        // Reviewer B tries to download a file via Reviewer A's assignment
        $file = SubmissionFile::create([
            'submission_revision_id' => $rev->id,
            'original_name' => 'ms.pdf',
            'file_path'     => 'uploads/ms.pdf',
            'size'          => 1024,
            'disk'          => 'local',
            'mime_type'     => 'application/pdf',
        ]);
        $dlResponse = $this->actingAs($reviewerB)
            ->get("/api/reviewer/assignments/{$assignA->id}/files/{$file->id}/download");
        $dlResponse->assertStatus(403);

        // Reviewer B tries accept on Reviewer A's assignment
        $acceptResponse = $this->actingAs($reviewerB)
            ->postJson("/api/reviewer/assignments/{$assignA->id}/accept");
        $acceptResponse->assertStatus(403);
    }

    // ─────────────────────────────────────────────────
    // Scenario 4: Blind review — author identity absent at API level
    // ─────────────────────────────────────────────────
    public function test_scenario_4_blind_review_no_author_identity_at_api_level()
    {
        $journal  = $this->makeJournal();
        $owner    = $this->makeMember($journal, 'owner');
        $reviewer = $this->makeMember($journal, 'reviewer');
        $author   = User::factory()->create(['name' => 'Secret Author', 'email' => 'secret@example.com']);

        $sub   = $this->makeSubmission($journal, $author, 'review_pending');
        $rev   = $this->makeRevision($sub);
        $round = $this->makeRound($rev);
        $this->makeAssignment($round, $reviewer, $owner, 'single_blind');

        $response = $this->actingAs($reviewer)
            ->getJson('/api/reviewer/assignments');

        $response->assertStatus(200);
        $submission = $response->json('data.0.submission');

        $this->assertNotNull($submission);
        $this->assertArrayNotHasKey('created_by', $submission, '[blind] created_by must not be exposed');
        $this->assertArrayNotHasKey('authors', $submission, '[blind] authors must not be exposed');

        // Also verify via detail endpoint
        $assignments = $response->json('data');
        $assignId = $assignments[0]['id'];
        $detail = $this->actingAs($reviewer)
            ->getJson("/api/reviewer/assignments/{$assignId}");
        $detail->assertStatus(200);
        $detailSub = $detail->json('data.submission');
        $this->assertArrayNotHasKey('created_by', $detailSub, '[blind detail] created_by must not be exposed');
        $this->assertArrayNotHasKey('authors', $detailSub, '[blind detail] authors must not be exposed');
    }

    // ─────────────────────────────────────────────────
    // Scenario 5: Exact revision — reviewer sees only their round's revision
    // ─────────────────────────────────────────────────
    public function test_scenario_5_reviewer_sees_exact_revision_files_not_latest()
    {
        $journal  = $this->makeJournal();
        $owner    = $this->makeMember($journal, 'owner');
        $reviewer = $this->makeMember($journal, 'reviewer');
        $author   = $this->makeMember($journal, 'owner');

        $sub  = $this->makeSubmission($journal, $author, 'review_pending');
        $rev1 = $this->makeRevision($sub);
        $rev2 = SubmissionRevision::create(['submission_id' => $sub->id, 'version_number' => 2]); // newer revision

        // Round is tied to rev1, not rev2
        $round  = $this->makeRound($rev1);
        $assign = $this->makeAssignment($round, $reviewer, $owner);
        $assign->update(['status' => 'accepted', 'accepted_at' => now()]);

        // File on rev2 (not the reviewer's revision)
        $fileOnRev2 = SubmissionFile::create([
            'submission_revision_id' => $rev2->id,
            'original_name' => 'rev2_draft.pdf',
            'file_path'     => 'uploads/rev2_draft.pdf',
            'size'          => 2048,
            'disk'          => 'local',
            'mime_type'     => 'application/pdf',
        ]);

        // Reviewer tries to access rev2's file through their (rev1) assignment
        $response = $this->actingAs($reviewer)
            ->get("/api/reviewer/assignments/{$assign->id}/files/{$fileOnRev2->id}/download");

        $response->assertStatus(403); // file does not belong to their revision
    }

    // ─────────────────────────────────────────────────
    // Scenario 6: Full review submission flow
    // ─────────────────────────────────────────────────
    public function test_scenario_6_full_review_submission_creates_peer_review_and_criterion_responses()
    {
        $journal  = $this->makeJournal();
        $owner    = $this->makeMember($journal, 'owner');
        $reviewer = $this->makeMember($journal, 'reviewer');
        $sub      = $this->makeSubmission($journal, $owner, 'review_pending');
        $rev      = $this->makeRevision($sub);
        $round    = $this->makeRound($rev);
        $assign   = $this->makeAssignment($round, $reviewer, $owner);
        $assign->update(['status' => 'accepted', 'accepted_at' => now()]);

        $criterion = ReviewCriterion::create([
            'journal_id' => $journal->id,
            'name'       => 'Methodology',
            'type'       => 'rating',
            'config'     => ['min' => 1, 'max' => 5],
            'status'     => 'active',
        ]);

        $response = $this->actingAs($reviewer)
            ->postJson("/api/reviewer/assignments/{$assign->id}/submit", [
                'recommendation'     => 'minor_revision',
                'comments_to_editor' => 'Editor comment.',
                'comments_to_author' => 'Author comment.',
                'criteria_responses' => [
                    ['criterion_id' => $criterion->id, 'response' => '4'],
                ],
            ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('review_assignments', ['id' => $assign->id, 'status' => 'submitted']);

        $peer = PeerReview::where('review_assignment_id', $assign->id)->first();
        $this->assertNotNull($peer);
        $this->assertEquals('minor_revision', $peer->recommendation);
        $this->assertNotNull($peer->submitted_at);

        $cr = ReviewCriterionResponse::where('peer_review_id', $peer->id)->first();
        $this->assertNotNull($cr);
        $this->assertEquals('4', $cr->response);
        $this->assertEquals($criterion->id, $cr->review_criterion_id);
    }

    // ─────────────────────────────────────────────────
    // Scenario 7: Submitted PeerReview is immutable
    // ─────────────────────────────────────────────────
    public function test_scenario_7_submitted_peer_review_cannot_be_modified()
    {
        $journal  = $this->makeJournal();
        $owner    = $this->makeMember($journal, 'owner');
        $reviewer = $this->makeMember($journal, 'reviewer');
        $sub      = $this->makeSubmission($journal, $owner, 'review_pending');
        $rev      = $this->makeRevision($sub);
        $round    = $this->makeRound($rev);
        $assign   = $this->makeAssignment($round, $reviewer, $owner);
        $assign->update(['status' => 'accepted', 'accepted_at' => now()]);

        $this->actingAs($reviewer)
            ->postJson("/api/reviewer/assignments/{$assign->id}/submit", [
                'recommendation' => 'accept',
            ])->assertStatus(200);

        // Re-submit attempt — assignment is now 'submitted'
        $response = $this->actingAs($reviewer)
            ->postJson("/api/reviewer/assignments/{$assign->id}/submit", [
                'recommendation' => 'reject',
            ]);
        $response->assertStatus(422);

        // Direct model mutation must throw
        $peer = PeerReview::where('review_assignment_id', $assign->id)->first();
        $this->expectException(\Exception::class);
        $peer->update(['recommendation' => 'reject']);
    }

    // ─────────────────────────────────────────────────
    // Scenario 8: Editorial decision locks round
    // ─────────────────────────────────────────────────
    public function test_scenario_8_editorial_decision_locks_round()
    {
        $journal  = $this->makeJournal();
        $owner    = $this->makeMember($journal, 'owner');
        $reviewer = $this->makeMember($journal, 'reviewer');
        $sub      = $this->makeSubmission($journal, $owner, 'review_pending');
        $rev      = $this->makeRevision($sub);
        $round    = $this->makeRound($rev);

        EditorialDecision::create([
            'review_round_id' => $round->id,
            'user_id'         => $owner->id,
            'decision'        => 'accept',
        ]);

        // Cannot add new assignment to locked round
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

    // ─────────────────────────────────────────────────
    // Scenario 9: No privilege escalation — reviewer cannot perform editorial operations
    // ─────────────────────────────────────────────────
    public function test_scenario_9_reviewer_cannot_perform_editorial_operations()
    {
        $journal  = $this->makeJournal();
        $owner    = $this->makeMember($journal, 'owner');
        $reviewer = $this->makeMember($journal, 'reviewer');
        $sub      = $this->makeSubmission($journal, $owner, 'editorial_assessment');
        $rev      = $this->makeRevision($sub);
        $round    = $this->makeRound($rev);

        // Reviewer cannot transition status
        $response = $this->actingAs($reviewer)
            ->patchJson("/api/editorial/submissions/{$sub->id}/status", [
                'status' => 'review_pending',
            ]);
        $response->assertStatus(403);

        // Reviewer cannot assign another reviewer
        $response2 = $this->actingAs($reviewer)
            ->postJson("/api/editorial/submissions/{$sub->id}/review-assignments", [
                'reviewer_id' => $reviewer->id,
                'review_mode' => 'single_blind',
            ]);
        $response2->assertStatus(403);

        // Reviewer cannot record editorial decision
        $response3 = $this->actingAs($reviewer)
            ->postJson("/api/editorial/submissions/{$sub->id}/rounds/{$round->id}/decision", [
                'decision' => 'accept',
            ]);
        $response3->assertStatus(403);

        // Submission.status is unchanged
        $this->assertEquals('editorial_assessment', $sub->fresh()->status);
    }
}
