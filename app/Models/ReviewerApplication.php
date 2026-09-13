<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReviewerApplication extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'available_for_review' => 'boolean',
        'agreed_confidentiality' => 'boolean',
        'agreed_conflict_of_interest' => 'boolean',
        'agreed_guidelines' => 'boolean',
        'reviewed_at' => 'datetime',
        'years_of_experience' => 'integer',
        'max_reviews_per_month' => 'integer',
    ];

    const STATUS_PENDING = 'pending';
    const STATUS_ACCEPTED = 'accepted';
    const STATUS_DENIED = 'denied';

    /**
     * Get the applicant.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the journal applied to.
     */
    public function journal(): BelongsTo
    {
        return $this->belongsTo(Journal::class, 'journal_id');
    }

    /**
     * Get the editor/admin who reviewed the application.
     */
    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
