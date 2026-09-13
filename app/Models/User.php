<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Traits\HasRoles;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject, MustVerifyEmail
{
    use HasFactory, Notifiable, SoftDeletes, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $guarded = ['id'];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    // jwt setup

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }
    public function getJWTCustomClaims()
    {
        return [];
    }

    // jwt setup

    public function profilePicture(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'media_id');
    }

    public function profilePicturePath(): Attribute
    {
        $profilePicture = asset('media/blank-user.png');

        if ($this->profilePicture && Storage::exists($this->profilePicture->src)) {
            $profilePicture = Storage::url($this->profilePicture->src);
        }

        return Attribute::make(
            get: fn() => $profilePicture,
        );
    }

    public function instructor(): HasOne
    {
        return $this->hasOne(Instructor::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }

    public function resetPasswords(): HasMany
    {
        return $this->hasMany(ResetPassword::class);
    }

    public function journalMemberships(): HasMany
    {
        return $this->hasMany(JournalMembership::class);
    }

    public function journals(): BelongsToMany
    {
        return $this->belongsToMany(Journal::class, 'journal_memberships')
                    ->withPivot(['role', 'status'])
                    ->withTimestamps();
    }

    public function blogs(): HasMany
    {
        return $this->hasMany(Blog::class);
    }

    public function accountActivation(): HasOne
    {
        return $this->hasOne(AccountActivation::class);
    }

    public function favouriteCourses(): BelongsToMany
    {
        return $this->belongsToMany(Course::class, 'user_courses');
    }

    public function favouriteInstructors(): BelongsToMany
    {
        return $this->belongsToMany(Instructor::class, 'user_instructors');
    }

    public function viewedContents(): HasMany
    {
        return $this->hasMany(UserContentView::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function fcmDeviceTokens(): HasMany
    {
        return $this->hasMany(FcmDeviceToken::class);
    }

    public function examSessions(): HasMany
    {
        return $this->hasMany(ExamSession::class);
    }

    public function answers(): HasMany
    {
        return $this->hasMany(Answer::class);
    }

    public function courseProgresses()
    {
        return $this->belongsToMany(Course::class, 'user_course_progresses')->withPivot('progress', 'course_id');
    }

    public function notificationInstances(): HasMany
    {
        return $this->hasMany(NotificationInstance::class, 'recipient_id');
    }

    public function signature()
    {
        return $this->belongsTo(Media::class, 'signature_id');
    }

    public function signaturePath(): Attribute
    {
        $signature = asset('enrollment/upload.png');

        if ($this->signature && Storage::exists($this->signature->src)) {
            $signature = Storage::url($this->signature->src);
        }

        return Attribute::make(
            get: fn() => $signature,
        );
    }

    public function reviewerApplications(): HasMany
    {
        return $this->hasMany(ReviewerApplication::class, 'user_id');
    }
}
