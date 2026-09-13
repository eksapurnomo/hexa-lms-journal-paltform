<?php

namespace Tests\Feature;

use App\Models\EditorialDecision;
use App\Models\PeerReview;
use App\Models\ReviewAssignment;
use App\Models\ReviewCriterion;
use App\Models\ReviewCriterionResponse;
use App\Models\ReviewRound;
use App\Models\Submission;
use App\Models\SubmissionEditorialEvent;
use App\Models\SubmissionFile;
use App\Models\SubmissionRevision;
use App\Models\User;
use App\Models\Journal;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Phase5FEImmutabilityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        \Illuminate\Support\Facades\Schema::disableForeignKeyConstraints();
    }

    public function test_cannot_update_submission_revision_after_review_starts()
    {
        $revision = SubmissionRevision::forceCreate(['submission_id' => 1, 'version_number' => 1]);
        $round = ReviewRound::forceCreate(['submission_revision_id' => $revision->id, 'round_number' => 1]);
        
        $revision->version_number = 2;
        $revision->save(); // Should pass
        
        ReviewAssignment::forceCreate(['review_round_id' => $round->id, 'reviewer_id' => 1, 'status' => 'assigned', 'review_mode' => 'single_blind', 'assigned_by' => 1]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('SubmissionRevision is immutable once a review process has started.');

        $revision->version_number = 3;
        $revision->save();
    }

    public function test_cannot_delete_submission_revision_after_review_starts()
    {
        $revision = SubmissionRevision::forceCreate(['submission_id' => 1, 'version_number' => 1]);
        $round = ReviewRound::forceCreate(['submission_revision_id' => $revision->id, 'round_number' => 1]);
        ReviewAssignment::forceCreate(['review_round_id' => $round->id, 'reviewer_id' => 1, 'status' => 'assigned', 'review_mode' => 'single_blind', 'assigned_by' => 1]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('SubmissionRevision cannot be deleted once a review process has started.');

        $revision->delete();
    }

    public function test_cannot_mutate_submission_file_after_review_starts()
    {
        $revision = SubmissionRevision::forceCreate(['submission_id' => 1, 'version_number' => 1]);
        $file = SubmissionFile::forceCreate(['submission_revision_id' => $revision->id, 'original_name' => 'test.pdf', 'disk' => 'local', 'file_path' => 'local/test.pdf']);
        $round = ReviewRound::forceCreate(['submission_revision_id' => $revision->id, 'round_number' => 1]);
        ReviewAssignment::forceCreate(['review_round_id' => $round->id, 'reviewer_id' => 1, 'status' => 'assigned', 'review_mode' => 'single_blind', 'assigned_by' => 1]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('SubmissionFile is immutable once its parent revision has entered review.');

        $file->original_name = 'hacked.pdf';
        $file->save();
    }

    public function test_cannot_create_assignment_on_locked_round()
    {
        $round = ReviewRound::forceCreate(['submission_revision_id' => 1, 'round_number' => 1]);
        EditorialDecision::forceCreate(['review_round_id' => $round->id, 'decision' => 'accept', 'user_id' => 1]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Cannot add assignments to a ReviewRound that is locked by an EditorialDecision.');

        ReviewAssignment::forceCreate(['review_round_id' => $round->id, 'reviewer_id' => 1, 'status' => 'assigned', 'review_mode' => 'single_blind', 'assigned_by' => 1]);
    }

    public function test_cannot_mutate_round_after_editorial_decision()
    {
        $round = ReviewRound::forceCreate(['submission_revision_id' => 1, 'round_number' => 1]);
        EditorialDecision::forceCreate(['review_round_id' => $round->id, 'decision' => 'accept', 'user_id' => 1]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('ReviewRound is locked and cannot be updated once an EditorialDecision is made.');

        $round->round_number = 2;
        $round->save();
    }

    public function test_cannot_mutate_submitted_assignment()
    {
        $assignment = ReviewAssignment::forceCreate(['review_round_id' => 1, 'reviewer_id' => 1, 'status' => 'submitted', 'review_mode' => 'single_blind', 'assigned_by' => 1]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Cannot change the status of a ReviewAssignment that has already been submitted.');

        $assignment->status = 'assigned';
        $assignment->save();
    }

    public function test_cannot_delete_submitted_assignment()
    {
        $assignment = ReviewAssignment::forceCreate(['review_round_id' => 1, 'reviewer_id' => 1, 'status' => 'submitted', 'review_mode' => 'single_blind', 'assigned_by' => 1]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Cannot delete a ReviewAssignment that has been submitted.');

        $assignment->delete();
    }

    public function test_cannot_mutate_submitted_peer_review()
    {
        $review = PeerReview::forceCreate(['review_assignment_id' => 1, 'recommendation' => 'accept', 'submitted_at' => now()]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Submitted PeerReview records are immutable.');

        $review->recommendation = 'reject';
        $review->save();
    }

    public function test_cannot_mutate_review_criterion_after_first_use()
    {
        $criterion = ReviewCriterion::forceCreate(['journal_id' => 1, 'name' => 'Original Name', 'type' => 'scale', 'config' => '[]']);
        $review = PeerReview::forceCreate(['review_assignment_id' => 1, 'recommendation' => 'accept', 'submitted_at' => now()]);
        
        ReviewCriterionResponse::forceCreate([
            'review_criterion_id' => $criterion->id,
            'peer_review_id' => $review->id,
            'response' => '5'
        ]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('ReviewCriterion cannot be substantively modified after it has been used in a review.');

        $criterion->name = 'Hacked Name';
        $criterion->save();
    }

    public function test_can_retire_review_criterion_after_first_use()
    {
        $criterion = ReviewCriterion::forceCreate(['journal_id' => 1, 'name' => 'Original Name', 'type' => 'scale', 'config' => '[]']);
        $review = PeerReview::forceCreate(['review_assignment_id' => 1, 'recommendation' => 'accept', 'submitted_at' => now()]);
        
        ReviewCriterionResponse::forceCreate([
            'review_criterion_id' => $criterion->id,
            'peer_review_id' => $review->id,
            'response' => '5'
        ]);

        $criterion->status = 'retired';
        $criterion->save();
        
        $criterion->delete();
        $this->assertSoftDeleted($criterion);
    }

    public function test_cannot_update_editorial_events()
    {
        Submission::forceCreate(['id' => 1, 'journal_id' => 1, 'created_by' => 1, 'title' => 'test', 'status' => 'draft']);
        $event = SubmissionEditorialEvent::forceCreate(['submission_id' => 1, 'user_id' => 1, 'action' => 'test', 'payload' => '[]']);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('SubmissionEditorialEvent records are append-only and cannot be updated.');

        $event->action = 'hacked';
        $event->save();
    }

    public function test_cannot_delete_editorial_events()
    {
        Submission::forceCreate(['id' => 2, 'journal_id' => 1, 'created_by' => 1, 'title' => 'test2', 'status' => 'draft']);
        $event = SubmissionEditorialEvent::forceCreate(['submission_id' => 2, 'user_id' => 1, 'action' => 'test', 'payload' => '[]']);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('SubmissionEditorialEvent records are append-only and cannot be deleted.');

        $event->delete();
    }

    public function test_cannot_update_editorial_decision()
    {
        $decision = EditorialDecision::forceCreate(['review_round_id' => 1, 'decision' => 'accept', 'user_id' => 1]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('EditorialDecision records are immutable and cannot be updated.');

        $decision->decision = 'reject';
        $decision->save();
    }

    public function test_cannot_delete_editorial_decision()
    {
        $decision = EditorialDecision::forceCreate(['review_round_id' => 1, 'decision' => 'accept', 'user_id' => 1]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('EditorialDecision records are immutable and cannot be deleted.');

        $decision->delete();
    }
}
