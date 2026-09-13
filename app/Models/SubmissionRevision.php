<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SubmissionRevision extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'submission_id',
        'version_number',
    ];

    public function submission()
    {
        return $this->belongsTo(Submission::class);
    }

    public function files()
    {
        return $this->hasMany(SubmissionFile::class);
    }

    public function reviewRounds()
    {
        return $this->hasMany(ReviewRound::class);
    }

    protected static function booted()
    {
        static::updating(function ($revision) {
            // A revision is immutable if it has been associated with a review round that has an assignment
            if ($revision->reviewRounds()->whereHas('assignments')->exists()) {
                throw new \Exception("SubmissionRevision is immutable once a review process has started.");
            }
        });

        static::deleting(function ($revision) {
            if ($revision->reviewRounds()->whereHas('assignments')->exists()) {
                throw new \Exception("SubmissionRevision cannot be deleted once a review process has started.");
            }
        });
    }
}
