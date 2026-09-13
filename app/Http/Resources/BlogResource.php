<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BlogResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user' => UserResource::make($this->user),
            'thumbnail' => $this->mediaPath,
            'title' => $this->title,
            'description' => $this->description,
            'status' => $this->status,
            'updated_at' => $this->updated_at?->format('d F, Y'),
        ];
    }
}
