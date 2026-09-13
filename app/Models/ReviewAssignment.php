<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class ReviewAssignment extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = ['id'];

    protected $casts = [
        'assigned_at' => 'datetime',
        'accepted_at' => 'datetime',
        'declined_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    public function reviewRound(): BelongsTo
    {
        return $this->belongsTo(ReviewRound::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }

    public function assigner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    public function peerReview(): HasOne
    {
        return $this->hasOne(PeerReview::class);
    }

    protected static function booted()
    {
        static::creating(function ($assignment) {
            $round = ReviewRound::find($assignment->review_round_id);
            if ($round) {
                if ($round->editorialDecision()->exists()) {
                    throw new \Exception("Cannot add assignments to a ReviewRound that is locked by an EditorialDecision.");
                }
                
                // Inherit review_model from the snapshot on ReviewRound
                $assignment->review_mode = $round->review_model;
            }
        });

        static::updating(function ($assignment) {
            if ($assignment->getOriginal('status') === 'submitted' && $assignment->isDirty('status')) {
                throw new \Exception("Cannot change the status of a ReviewAssignment that has already been submitted.");
            }
        });

        static::deleting(function ($assignment) {
            if ($assignment->status === 'submitted') {
                throw new \Exception("Cannot delete a ReviewAssignment that has been submitted.");
            }
        });
    }
}
