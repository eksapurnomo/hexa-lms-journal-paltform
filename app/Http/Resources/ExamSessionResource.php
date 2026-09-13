<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ExamSessionResource extends JsonResource
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
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
            'exam_duration' => intval($this->exam->duration) * 60,
            'total_mark' => $this->total_mark,
            'pass_mark' => $this->exam->pass_marks,
            'obtained_mark' => $this->obtained_mark ?? 0,
            'submitted' => $this->submitted,
        ];
    }
}
