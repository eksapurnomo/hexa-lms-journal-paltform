<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class AcademicTaxonomyNode extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'is_active' => 'boolean',
        'metadata' => 'array',
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(AcademicTaxonomyNode::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(AcademicTaxonomyNode::class, 'parent_id');
    }

    public function journals(): BelongsToMany
    {
        return $this->belongsToMany(Journal::class, 'journal_subjects', 'taxonomy_node_id', 'journal_id')
                    ->withPivot('type')
                    ->withTimestamps();
    }

    public function academicProfiles(): BelongsToMany
    {
        return $this->belongsToMany(AcademicProfile::class, 'academic_profile_expertise', 'taxonomy_node_id', 'academic_profile_id')
                    ->withTimestamps();
    }
}