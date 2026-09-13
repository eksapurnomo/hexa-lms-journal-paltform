<?php

namespace App\Http\Resources;

use App\Repositories\SettingRepository;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NotificationInstanceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $setting = SettingRepository::query()->get()->first();

        return [
            'id' => $this->id,
            'logo' => $setting->faviconPath,
            "course_id" => $this->course_id,
            'type' => $this->notification->type,
            'heading' => $this->notification->heading,
            'title' => $this->title,
            'content' => $this->content,
            'is_read' => $this->is_read,
            "created_at" => $this->created_at->diffForHumans(),
            "date_format" => $this->created_at->format('d M, Y'),
            'raw_time' => $this->created_at,
        ];
    }
}
