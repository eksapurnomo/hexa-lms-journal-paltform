<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubmissionFile extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function revision(): BelongsTo
    {
        return $this->belongsTo(SubmissionRevision::class, 'submission_revision_id');
    }

    protected static function booted()
    {
        static::updating(function ($file) {
            if ($file->revision && $file->revision->reviewRounds()->whereHas('assignments')->exists()) {
                throw new \Exception("SubmissionFile is immutable once its parent revision has entered review.");
            }
        });

        static::deleting(function ($file) {
            if ($file->revision && $file->revision->reviewRounds()->whereHas('assignments')->exists()) {
                throw new \Exception("SubmissionFile cannot be deleted once its parent revision has entered review.");
            }
        });
    }
}
