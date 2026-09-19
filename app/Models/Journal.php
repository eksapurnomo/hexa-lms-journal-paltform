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

    public function subjects(): BelongsToMany
    {
        return $this->belongsToMany(AcademicTaxonomyNode::class, 'journal_subjects', 'journal_id', 'taxonomy_node_id')
                    ->withPivot('type')
                    ->withTimestamps();
    }

    public function primarySubjects(): BelongsToMany
    {
        return $this->subjects()->wherePivot('type', 'primary');
    }

    public function secondarySubjects(): BelongsToMany
    {
        return $this->subjects()->wherePivot('type', 'secondary');
    }

    public function researchTopics(): BelongsToMany
    {
        return $this->subjects()->wherePivot('type', 'topic');
    }

    public function membershipApplications(): HasMany
    {
        return $this->hasMany(JournalMembershipApplication::class);
    }

    public function getSelectorLabelAttribute(): string
    {
        // Use already-eager-loaded collection (avoids N+1 when iterated in views)
        $primary = $this->primarySubjects->first();
        if (!$primary) {
            return $this->title;
        }

        $names = [];
        $current = $primary;
        while ($current) {
            array_unshift($names, $current->name);
            // Use relationLoaded() to avoid re-querying; relies on eager-loaded parent chain
            $current = $current->relationLoaded('parent') ? $current->parent : null;
        }

        $names[] = $this->title;
        return implode(' › ', $names);
    }
}
