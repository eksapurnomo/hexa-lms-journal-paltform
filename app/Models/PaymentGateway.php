<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class PaymentGateway extends Model
{
    use HasFactory;
    protected $casts = [
        'config' => 'array',
    ];

    protected $guarded = ['id'];

    public function image(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'media_id');
    }

    public function imagePath(): Attribute
    {
        $image = asset('media/dummy-image.jpg');

        if (file_exists(public_path($this->image->src))) {
            $image = asset($this->image->src);
        }

        return Attribute::make(
            get: fn () => $image,
        );
    }
}
