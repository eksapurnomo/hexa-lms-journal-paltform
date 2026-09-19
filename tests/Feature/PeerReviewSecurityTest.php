<?php

namespace Tests\Feature;

use App\Models\Journal;
use App\Models\JournalMembership;
use App\Models\Submission;
use App\Models\SubmissionFile;
use App\Models\User;
use App\Models\ReviewAssignment;
use App\Models\PeerReview;
use App\Models\SubmissionEditorialEvent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PeerReviewSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        if (config('database.default') !== 'mysql' || config('database.connections.mysql.database') !== 'readylms_testing') {
            $this->markTestSkipped('Not using testing database.');
        }
        
        Storage::fake('local');
    }

    private function createAdmin()
    {
        return User::factory()->create(['is_admin' => true]);
    }

    private function createSetup()
    {
        $journal = Journal::create([
            'title' => 'Test Journal',
            'slug' => uniqid(),
            'status' => 'active',
            'is_public' => true,
        ]);

        $owner = User::factory()->create();
        JournalMembership::create(['journal_id' => $journal->id, 'user_id' => $owner->id, 'role' => 'owner', 'status' => 'active']);

        $editor = User::factory()->create();
        JournalMembership::create(['journal_id' => $journal->id, 'user_id' => $editor->id, 'role' => 'editor', 'status' => 'active']);

        $reviewer = User::factory()->create();
        JournalMembership::create(['journal_id' => $journal->id, 'user_id' => $reviewer->id, 'role' => 'reviewer', 'status' => 'active']);
        \App\Models\ReviewerApplication::create([
            'user_id' => $reviewer->id, 'journal_id' => $journal->id, 'status' => 'accepted',
            'affiliation' => 'A', 'department' => 'D', 'academic_position' => 'P', 'primary_research_area' => 'R', 'years_of_experience' => 1
        ]);
        \App\Models\AcademicProfile::factory()->create(['user_id' => $reviewer->id]);

        $otherReviewer = User::factory()->create();
        JournalMembership::create(['journal_id' => $journal->id, 'user_id' => $otherReviewer->id, 'role' => 'reviewer', 'status' => 'active']);
        \App\Models\ReviewerApplication::create([
            'user_id' => $otherReviewer->id, 'journal_id' => $journal->id, 'status' => 'accepted',
            'affiliation' => 'A', 'department' => 'D', 'academic_position' => 'P', 'primary_research_area' => 'R', 'years_of_experience' => 1
        ]);
        \App\Models\AcademicProfile::factory()->create(['user_id' => $otherReviewer->id]);

        $author = User::factory()->create();

        $submission = Submission::forceCreate([
            'journal_id' => $journal->id,
            'title' => 'Test Submission',
            'abstract' => 'Test Abstract',
            'status' => 'review_pending',
            'created_by' => $author->id,
        ]);

        $revision = \App\Models\SubmissionRevision::create([
            'submission_id' => $submission->id,
            'version_number' => 1,
        ]);

        $round = \App\Models\ReviewRound::create([
            'submission_revision_id' => $revision->id,
            'round_number' => 1,
        ]);

        return compact('journal', 'owner', 'editor', 'reviewer', 'otherReviewer', 'author', 'submission', 'revision', 'round');
    }

    public function test_assignment_authorization()
    {
        extract($this->createSetup());
        $admin = $this->createAdmin();

        // Admin allow
        $response = $this->actingAs($admin)->postJson("/api/editorial/submissions/{$submission->id}/review-assignments", [
            'reviewer_id' => $reviewer->id,
        ]);
        $response->assertStatus(200);
        ReviewAssignment::query()->delete();

        // Owner allow
        $response = $this->actingAs($owner)->postJson("/api/editorial/submissions/{$submission->id}/review-assignments", [
            'reviewer_id' => $reviewer->id,
        ]);
        $response->assertStatus(200);
        ReviewAssignment::query()->delete();

        // Unassigned Editor deny
        $response = $this->actingAs($editor)->postJson("/api/editorial/submissions/{$submission->id}/review-assignments", [
            'reviewer_id' => $reviewer->id,
        ]);
        $response->assertStatus(403);

        // Assign editor
        $submission->forceFill(['editor_id' => $editor->id])->save();

        // Assigned Editor allow
        $response = $this->actingAs($editor)->postJson("/api/editorial/submissions/{$submission->id}/review-assignments", [
            'reviewer_id' => $reviewer->id,
        ]);
        $response->assertStatus(200);

        // Reviewer deny
        $response = $this->actingAs($reviewer)->postJson("/api/editorial/submissions/{$submission->id}/review-assignments", [
            'reviewer_id' => $otherReviewer->id,
        ]);
        $response->assertStatus(403);

        // Author deny
        $response = $this->actingAs($author)->postJson("/api/editorial/submissions/{$submission->id}/review-assignments", [
            'reviewer_id' => $otherReviewer->id,
        ]);
        $response->assertStatus(403);
    }

    public function test_reviewer_eligibility()
    {
        extract($this->createSetup());
        $admin = $this->createAdmin();

        $inactiveReviewer = User::factory()->create();
        JournalMembership::create(['journal_id' => $journal->id, 'user_id' => $inactiveReviewer->id, 'role' => 'reviewer', 'status' => 'inactive']);

        $otherJournal = Journal::create(['title' => 'Other Journal', 'slug' => 'other-journal', 'status' => 'active', 'is_public' => true]);
        $otherJournalReviewer = User::factory()->create();
        JournalMembership::create(['journal_id' => $otherJournal->id, 'user_id' => $otherJournalReviewer->id, 'role' => 'reviewer', 'status' => 'active']);

        $response = $this->actingAs($admin)->getJson("/api/editorial/submissions/{$submission->id}/eligible-reviewers");
        $response->assertStatus(200);

        $ids = collect($response->json('data'))->pluck('id')->toArray();
        
        $this->assertContains($reviewer->id, $ids);
        $this->assertContains($otherReviewer->id, $ids);
        
        $this->assertNotContains($inactiveReviewer->id, $ids);
        $this->assertNotContains($otherJournalReviewer->id, $ids);
        $this->assertNotContains($editor->id, $ids);
        $this->assertNotContains($author->id, $ids);
    }

    public function test_reviewer_isolation()
    {
        extract($this->createSetup());

        $assignment = ReviewAssignment::create([
            'review_round_id' => $round->id,
            'reviewer_id' => $reviewer->id,
            'assigned_by' => $owner->id,
            'status' => 'assigned',
        ]);

        $this->actingAs($reviewer)->getJson("/api/reviewer/assignments/{$assignment->id}")->assertStatus(200);
        $this->actingAs($otherReviewer)->getJson("/api/reviewer/assignments/{$assignment->id}")->assertStatus(403);
    }

    public function test_review_lifecycle()
    {
        extract($this->createSetup());

        $assignment = ReviewAssignment::create([
            'review_round_id' => $round->id,
            'reviewer_id' => $reviewer->id,
            'assigned_by' => $owner->id,
            'status' => 'assigned',
        ]);

        // Accept
        $this->actingAs($reviewer)->postJson("/api/reviewer/assignments/{$assignment->id}/accept")->assertStatus(200);
        $this->assertEquals('accepted', $assignment->fresh()->status);

        // Submit Review
        $this->actingAs($reviewer)->postJson("/api/reviewer/assignments/{$assignment->id}/submit", [
            'recommendation' => 'accept',
            'comments_to_editor' => 'Looks good',
            'comments_to_author' => 'Great job'
        ])->assertStatus(200);
        
        $this->assertEquals('submitted', $assignment->fresh()->status);
        $this->assertEquals('review_pending', $submission->fresh()->status);
        $this->assertNotNull($assignment->peerReview);

        // Review Immutability
        $this->actingAs($reviewer)->postJson("/api/reviewer/assignments/{$assignment->id}/submit", [
            'recommendation' => 'reject'
        ])->assertStatus(422);
    }

    public function test_manuscript_security()
    {
        extract($this->createSetup());

        $file = SubmissionFile::forceCreate([
            'submission_revision_id' => $revision->id,
            'disk' => 'local',
            'file_path' => 'submissions/' . $submission->id . '/test.pdf',
            'original_name' => 'test.pdf',
            'mime_type' => 'application/pdf',
            'size' => 1024,
        ]);
        Storage::disk('local')->put($file->file_path, 'dummy content');

        $assignment = ReviewAssignment::create([
            'review_round_id' => $round->id,
            'reviewer_id' => $reviewer->id,
            'assigned_by' => $owner->id,
            'status' => 'assigned',
        ]);

        $this->actingAs($otherReviewer)->getJson("/api/reviewer/assignments/{$assignment->id}/files/{$file->id}/download")->assertStatus(403);
        $this->actingAs($reviewer)->getJson("/api/reviewer/assignments/{$assignment->id}/files/{$file->id}/download")->assertStatus(403);

        $assignment->update(['status' => 'accepted']);
        $this->actingAs($reviewer)->getJson("/api/reviewer/assignments/{$assignment->id}/files/{$file->id}/download")->assertStatus(200);

        // Wrong file binding deny
        $otherSubmission = Submission::forceCreate([
            'journal_id' => $journal->id,
            'title' => 'Other',
            'status' => 'draft',
            'created_by' => $author->id
        ]);
        $otherRevision = \App\Models\SubmissionRevision::create(['submission_id' => $otherSubmission->id, 'version_number' => 1]);
        $otherFile = SubmissionFile::forceCreate([
            'submission_revision_id' => $otherRevision->id,
            'disk' => 'local',
            'file_path' => 'submissions/' . $otherSubmission->id . '/other.pdf',
            'original_name' => 'other.pdf',
            'mime_type' => 'application/pdf',
            'size' => 1024,
        ]);
        
        $this->actingAs($reviewer)->getJson("/api/reviewer/assignments/{$assignment->id}/files/{$otherFile->id}/download")->assertStatus(403);
    }

    public function test_blind_review()
    {
        extract($this->createSetup());
        
        $round->update(['review_model' => 'open']);
        $assignmentOpen = ReviewAssignment::create([
            'review_round_id' => $round->id,
            'reviewer_id' => $reviewer->id,
            'assigned_by' => $owner->id,
            'status' => 'assigned',
        ]);

        $resOpen = $this->actingAs($reviewer)->getJson("/api/reviewer/assignments/{$assignmentOpen->id}");
        $this->assertArrayHasKey('authors', $resOpen->json('data.submission'));

        $roundBlind = \App\Models\ReviewRound::create([
            'submission_revision_id' => $revision->id,
            'round_number' => 2,
            'review_model' => 'double_blind',
        ]);

        $assignmentBlind = ReviewAssignment::create([
            'review_round_id' => $roundBlind->id,
            'reviewer_id' => $otherReviewer->id,
            'assigned_by' => $owner->id,
            'status' => 'assigned',
        ]);

        $resBlind = $this->actingAs($otherReviewer)->getJson("/api/reviewer/assignments/{$assignmentBlind->id}");
        $this->assertArrayNotHasKey('authors', $resBlind->json('data.submission'));
    }

    public function test_audit()
    {
        extract($this->createSetup());
        $admin = $this->createAdmin();

        $this->actingAs($admin)->postJson("/api/editorial/submissions/{$submission->id}/review-assignments", [
            'reviewer_id' => $reviewer->id,
        ]);

        $this->assertDatabaseHas('submission_editorial_events', [
            'submission_id' => $submission->id,
            'user_id' => $admin->id,
            'action' => SubmissionEditorialEvent::ACTION_REVIEWER_ASSIGNED,
        ]);
    }
}
