<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Chapter extends Model
{
    protected $guarded = ['id'];

    use HasFactory, SoftDeletes;

    protected static function boot()
    {
        parent::boot();

        // Order chapters with serial number by default
        static::addGlobalScope('serial_number', function ($builder) {
            $builder->orderBy('serial_number');
        });
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function contents(): HasMany
    {
        return $this->hasMany(Content::class);
    }
}
