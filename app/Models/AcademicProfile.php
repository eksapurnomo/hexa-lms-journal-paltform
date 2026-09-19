<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AcademicProfile extends Model
{
    use HasFactory;

    public const TYPE_LECTURER = 'lecturer';
    public const TYPE_RESEARCHER = 'researcher';

    public const INST_UNIVERSITY = 'university';
    public const INST_RESEARCH_INSTITUTE = 'research_institute';
    public const INST_GOVERNMENT = 'government_research';
    public const INST_NGO = 'ngo';
    public const INST_PRIVATE = 'private_research';
    public const INST_THINK_TANK = 'think_tank';
    public const INST_OTHER = 'other';
    public const INST_INDEPENDENT = 'independent';

    protected $guarded = ['id'];

    protected $casts = [
        'expertise' => 'array',
    ];

    /**
     * Get the user that owns the academic profile.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function institutionRelation(): BelongsTo
    {
        return $this->belongsTo(Institution::class, 'institution_id');
    }

    public function taxonomyExpertise(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(AcademicTaxonomyNode::class, 'academic_profile_expertise', 'academic_profile_id', 'taxonomy_node_id')
                    ->withTimestamps();
    }
}
