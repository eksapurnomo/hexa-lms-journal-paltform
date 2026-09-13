<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Guest extends Model
{
    protected $guarded = ['id'];

    use HasFactory;

    public function favouriteCourses(): BelongsToMany
    {
        return $this->belongsToMany(Course::class, 'guest_courses');
    }

    public function favouriteInstructors(): BelongsToMany
    {
        return $this->belongsToMany(Instructor::class, 'guest_instructors');
    }
}
