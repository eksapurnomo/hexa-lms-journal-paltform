<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
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
            'title' => $this->title,
            'image' => $this->imagePath,
            'is_featured' => $this->is_featured,
            'color' => $this->color,
            'course_count' => $this->courses->count(),
            'display_order' => $this->display_order,
        ];
    }
}
