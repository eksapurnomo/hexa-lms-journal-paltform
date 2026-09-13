<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class ManageCertificate extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function siteLogo(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'site_logo_id');
    }

    public function siteLogoPath(): Attribute
    {
        $siteLogo = asset('enrollment/logo.png');

        if ($this->siteLogo && Storage::exists($this->siteLogo->src)) {
            $siteLogo = Storage::url($this->siteLogo->src);
        }

        return Attribute::make(
            get: fn() => $siteLogo,
        );
    }
    public function subSiteLogo(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'subsite_logo_id');
    }

    public function subSiteLogoPath(): Attribute
    {
        $subSiteLogo = asset('enrollment/logo.png');

        if ($this->subSiteLogo && Storage::exists($this->subSiteLogo->src)) {
            $subSiteLogo = Storage::url($this->subSiteLogo->src);
        }

        return Attribute::make(
            get: fn() => $subSiteLogo,
        );
    }

    public function authSignature(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'author_signature_id');
    }

    public function authSignaturePath(): Attribute
    {
        $authSignature = asset('enrollment/signature.jpg');

        if ($this->authSignature && Storage::exists($this->authSignature->src)) {
            $authSignature = Storage::url($this->authSignature->src);
        }

        return Attribute::make(
            get: fn() => $authSignature,
        );
    }

    public function frame(): BelongsTo
    {
        return $this->belongsTo(Frame::class, 'frame_id');
    }

    public function framePath(): Attribute
    {
        $frame = asset('enrollment/themeone.png');

        if ($this->frame && Storage::exists($this->frame->src)) {
            $frame = Storage::url($this->frame->src);
        }

        return Attribute::make(
            get: fn() => $frame,
        );
    }
}
