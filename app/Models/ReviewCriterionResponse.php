<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReviewCriterionResponse extends Model
{
    use HasFactory;

    protected $fillable = [
        'peer_review_id',
        'review_criterion_id',
        'response',
    ];

    public function peerReview()
    {
        return $this->belongsTo(PeerReview::class);
    }

    public function reviewCriterion()
    {
        return $this->belongsTo(ReviewCriterion::class);
    }

    protected static function booted()
    {
        static::updating(function ($response) {
            if ($response->peerReview && $response->peerReview->submitted_at !== null) {
                throw new \Exception("ReviewCriterionResponse cannot be modified after the PeerReview has been submitted.");
            }
        });

        static::deleting(function ($response) {
            if ($response->peerReview && $response->peerReview->submitted_at !== null) {
                throw new \Exception("ReviewCriterionResponse cannot be deleted after the PeerReview has been submitted.");
            }
        });
    }
}
