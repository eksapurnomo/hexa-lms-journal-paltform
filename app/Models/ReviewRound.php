<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ReviewRound extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'submission_revision_id',
        'round_number',
        'review_model',
        'minimum_reviewers',
        'target_reviewers',
        'maximum_reviewers',
    ];

    public function submissionRevision()
    {
        return $this->belongsTo(SubmissionRevision::class);
    }

    public function assignments()
    {
        return $this->hasMany(ReviewAssignment::class);
    }

    public function editorialDecision()
    {
        return $this->hasOne(EditorialDecision::class);
    }

    protected static function booted()
    {
        static::creating(function ($round) {
            // Snapshot policy from Journal if not explicitly provided
            if (empty($round->review_model)) {
                $journal = null;
                if ($round->submissionRevision && $round->submissionRevision->submission) {
                    $journal = $round->submissionRevision->submission->journal;
                } elseif ($round->submission_revision_id) {
                    $revision = SubmissionRevision::with('submission.journal.peerReviewPolicy')->find($round->submission_revision_id);
                    $journal = $revision && $revision->submission ? $revision->submission->journal : null;
                }

                if ($journal && $journal->peerReviewPolicy) {
                    $policy = $journal->peerReviewPolicy;
                    $round->review_model = $policy->review_model;
                    $round->minimum_reviewers = $policy->minimum_reviewers;
                    $round->target_reviewers = $policy->target_reviewers;
                    $round->maximum_reviewers = $policy->maximum_reviewers;
                } else {
                    $round->review_model = PeerReviewPolicy::DEFAULT_REVIEW_MODEL;
                    $round->minimum_reviewers = PeerReviewPolicy::DEFAULT_MIN_REVIEWERS;
                    $round->target_reviewers = PeerReviewPolicy::DEFAULT_TARGET_REVIEWERS;
                    $round->maximum_reviewers = PeerReviewPolicy::DEFAULT_MAX_REVIEWERS;
                }
            }
        });

        static::updating(function ($round) {
            if ($round->editorialDecision()->exists()) {
                $dirty = $round->getDirty();
                unset($dirty['deleted_at']);
                if (count($dirty) > 0) {
                    throw new \Exception("ReviewRound is locked and cannot be updated once an EditorialDecision is made.");
                }
            }
        });

        static::deleting(function ($round) {
            if ($round->editorialDecision()->exists()) {
                throw new \Exception("ReviewRound is locked and cannot be deleted once an EditorialDecision is made.");
            }
        });
    }
}
