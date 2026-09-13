<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ReviewCriterion extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'journal_id',
        'name',
        'type',
        'config',
        'status',
    ];

    protected $casts = [
        'config' => 'array',
    ];

    public function journal()
    {
        return $this->belongsTo(Journal::class);
    }

    protected static function booted()
    {
        static::updating(function ($criterion) {
            $dirty = $criterion->getDirty();
            // We allow soft deletes (deleted_at) and status changes (e.g. retiring the criterion)
            unset($dirty['deleted_at']);
            unset($dirty['updated_at']);
            unset($dirty['status']);
            
            if (count($dirty) > 0 && ReviewCriterionResponse::where('review_criterion_id', $criterion->id)->exists()) {
                throw new \Exception("ReviewCriterion cannot be substantively modified after it has been used in a review. Please retire it and create a new one instead.");
            }
        });
    }
}
