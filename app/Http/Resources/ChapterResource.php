<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ChapterResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'title' => $this->title,
            'serial_number' => $this->serial_number,
            'total_duration' => $this->contents->sum('duration'),
            'duration' => $this->contents->sum('duration'),
            'contents' => ContentResource::collection($this->contents),
        ];
    }
}
