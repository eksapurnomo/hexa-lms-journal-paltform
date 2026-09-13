<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Journal extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function peerReviewPolicy(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(PeerReviewPolicy::class);
    }

    public function memberships(): HasMany
    {
        return $this->hasMany(JournalMembership::class);
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'journal_memberships')
                    ->withPivot(['role', 'status'])
                    ->withTimestamps();
    }
}
