<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Submission extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = ['id', 'status', 'editor_id', 'created_by', 'journal_id'];

    protected $casts = [
        'keywords' => 'array',
        'submitted_at' => 'datetime',
    ];

    const STATUS_DRAFT = 'draft';
    const STATUS_SUBMITTED = 'submitted';
    const STATUS_EDITORIAL_ASSESSMENT = 'editorial_assessment';
    const STATUS_REVISION_REQUIRED = 'revision_required';
    const STATUS_REVISION_SUBMITTED = 'revision_submitted';
    const STATUS_REJECTED = 'rejected';
    const STATUS_REVIEW_PENDING = 'review_pending';
    const STATUS_ACCEPTED = 'accepted';

    public function journal(): BelongsTo
    {
        return $this->belongsTo(Journal::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function editor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'editor_id');
    }

    public function authors(): HasMany
    {
        return $this->hasMany(SubmissionAuthor::class);
    }

    public function revisions(): HasMany
    {
        return $this->hasMany(SubmissionRevision::class);
    }

    public function files()
    {
        return $this->hasManyThrough(SubmissionFile::class, SubmissionRevision::class);
    }

    public function editorialEvents(): HasMany
    {
        return $this->hasMany(SubmissionEditorialEvent::class);
    }
}
