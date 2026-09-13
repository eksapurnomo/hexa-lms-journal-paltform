<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Setting extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function logo(): BelongsTo
    {
        return $this->belongsTo(Media::class);
    }

    public function logoPath(): Attribute
    {
        $logo = asset('assets/images/logo-new.png');

        if ($this->logo && Storage::exists($this->logo->src)) {
            $logo = Storage::url($this->logo->src);
        }

        return Attribute::make(
            get: fn() => $logo,
        );
    }

    public function footer(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'footerlogo_id');
    }
    public function footerPath(): Attribute
    {
        $footer = asset('assets/images/logo-new.png');

        if ($this->footer && Storage::exists($this->footer->src)) {
            $footer = Storage::url($this->footer->src);
        }

        return Attribute::make(
            get: fn() => $footer,
        );
    }

    public function favicon(): BelongsTo
    {
        return $this->belongsTo(Media::class);
    }

    public function faviconPath(): Attribute
    {
        $favicon = asset('assets/images/favicon.ico');

        if ($this->favicon && Storage::exists($this->favicon->src)) {
            $favicon = Storage::url($this->favicon->src);
        }

        return Attribute::make(
            get: fn() => $favicon,
        );
    }

    public function scaner(): BelongsTo
    {
        return $this->belongsTo(Media::class);
    }

    public function scanerPath(): Attribute
    {
        $scaner = asset('assets/website/scaner/scan.png');

        if ($this->scaner && Storage::exists($this->scaner->src)) {
            $scaner = Storage::url($this->scaner->src);
        }

        return Attribute::make(
            get: fn() => $scaner,
        );
    }
}
