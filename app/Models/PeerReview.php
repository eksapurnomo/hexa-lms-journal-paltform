<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class PeerReview extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = ['id'];

    protected $casts = [
        'submitted_at' => 'datetime',
    ];

    public function reviewAssignment(): BelongsTo
    {
        return $this->belongsTo(ReviewAssignment::class);
    }

    public function criterionResponses()
    {
        return $this->hasMany(ReviewCriterionResponse::class);
    }

    protected static function booted()
    {
        static::updating(function ($review) {
            if ($review->getOriginal('submitted_at') !== null && $review->isDirty()) {
                throw new \Exception("Submitted PeerReview records are immutable.");
            }
        });
    }
}
