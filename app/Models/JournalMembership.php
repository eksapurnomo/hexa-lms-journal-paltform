<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JournalMembership extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function journal(): BelongsTo
    {
        return $this->belongsTo(Journal::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reviewerCapability(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(JournalReviewerCapability::class);
    }
}
