<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class ContentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $user = Auth::guard('api')->user();
        // dd($this->viewedUsers);

        return [
            'id' => $this->id,
            'title' => $this->title,
            'type' => $this->type,
            'duration' => $this->duration,
            'media' => $this->mediaPath,
            'media_id' => $this->media_id,
            'media_link' => $this->media_link,
            'is_forwardable' => $this->is_forwardable,
            'is_free' => $this->is_free,
            'serial_number' => $this->serial_number,
            'file_extension' => $this->media?->extension,
            'file_name_with_extension' => str_replace($this->media?->path . '/', '', $this->media?->src),
            'file_name_without_extension' => str_replace('.' . $this->media?->extension, '', str_replace($this->media?->path . '/', '', $this->media?->src)),
            'is_viewed' => $this->contentViews->contains('user_id', $user?->id),
            'media_updated_at' => $this->media_updated_at
        ];
    }
}
