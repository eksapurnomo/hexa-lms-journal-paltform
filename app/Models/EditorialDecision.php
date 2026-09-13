<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Exception;

class EditorialDecision extends Model
{
    use HasFactory;

    protected $fillable = [
        'review_round_id',
        'user_id',
        'decision',
        'comments',
    ];

    protected static function booted()
    {
        static::updating(function ($decision) {
            throw new Exception("EditorialDecision records are immutable and cannot be updated.");
        });
        
        static::deleting(function ($decision) {
            throw new Exception("EditorialDecision records are immutable and cannot be deleted.");
        });
    }

    public function reviewRound()
    {
        return $this->belongsTo(ReviewRound::class);
    }

    public function editor()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
