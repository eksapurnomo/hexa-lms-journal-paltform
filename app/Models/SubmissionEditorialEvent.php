<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubmissionEditorialEvent extends Model
{
    protected $fillable = [
        'submission_id',
        'user_id',
        'action',
        'payload',
    ];

    protected $casts = [
        'payload' => 'array',
    ];

    const ACTION_ASSIGNED = 'assigned';
    const ACTION_REASSIGNED = 'reassigned';
    const ACTION_SUBMITTED_TO_EDITORIAL_ASSESSMENT = 'submitted_to_editorial_assessment';
    const ACTION_REVISION_REQUESTED = 'revision_requested';
    const ACTION_REVISION_SUBMITTED = 'revision_submitted';
    const ACTION_REJECTED = 'rejected';
    const ACTION_SENT_TO_REVIEW_PENDING = 'sent_to_review_pending';
    const ACTION_REVIEWER_ASSIGNED = 'reviewer_assigned';
    const ACTION_REVIEWER_REASSIGNED = 'reviewer_reassigned';
    const ACTION_REVIEWER_CANCELLED = 'reviewer_cancelled';
    const ACTION_REVIEW_INVITATION_ACCEPTED = 'review_invitation_accepted';
    const ACTION_REVIEW_INVITATION_DECLINED = 'review_invitation_declined';
    const ACTION_REVIEW_STARTED = 'review_started';
    const ACTION_REVIEW_SUBMITTED = 'review_submitted';

    public function submission(): BelongsTo
    {
        return $this->belongsTo(Submission::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    protected static function booted()
    {
        static::updating(function ($event) {
            throw new \Exception("SubmissionEditorialEvent records are append-only and cannot be updated.");
        });

        static::deleting(function ($event) {
            throw new \Exception("SubmissionEditorialEvent records are append-only and cannot be deleted.");
        });
    }
}
