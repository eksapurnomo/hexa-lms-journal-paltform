<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PeerReviewPolicy extends Model
{
    public const DEFAULT_REVIEW_MODEL = 'double_blind';
    public const DEFAULT_MIN_REVIEWERS = 2;
    public const DEFAULT_TARGET_REVIEWERS = 2;
    public const DEFAULT_MAX_REVIEWERS = 4;

    protected $guarded = ['id'];

    protected $casts = [
        'minimum_reviewers' => 'integer',
        'target_reviewers' => 'integer',
        'maximum_reviewers' => 'integer',
        'reviewer_agreement_required' => 'boolean',
        'conflict_of_interest_required' => 'boolean',
        'confidentiality_required' => 'boolean',
    ];

    public function journal(): BelongsTo
    {
        return $this->belongsTo(Journal::class);
    }
}
