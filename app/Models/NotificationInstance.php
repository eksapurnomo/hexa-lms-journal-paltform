<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationInstance extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function notification(): BelongsTo
    {
        return $this->belongsTo(Notification::class);
    }
    public function course(): BelongsTo
    {
        return $this->belongsTo(course::class);
    }
}
