<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JournalReviewerCapability extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'available_for_review' => 'boolean',
        'max_reviews_per_month' => 'integer',
        'years_of_experience' => 'integer',
    ];

    /**
     * Get the membership that this capability record describes.
     */
    public function journalMembership(): BelongsTo
    {
        return $this->belongsTo(JournalMembership::class, 'journal_membership_id');
    }
}
