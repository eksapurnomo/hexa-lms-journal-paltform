<?php

namespace App\Models;

use App\Enum\NotificationTypeEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Notification extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'type' => NotificationTypeEnum::class,
    ];

    public function notificationInstances(): HasMany
    {
        return $this->hasMany(NotificationInstance::class);
    }
}
