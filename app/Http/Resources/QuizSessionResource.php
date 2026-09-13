<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QuizSessionResource extends JsonResource
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
            'seen_question_ids' => json_decode($this->seen_question_ids) ?? [],
            'answered_question_ids' => json_decode($this->answered_question_ids) ?? [],
            'right_answer_count' => $this->right_answer_count ?? 0,
            'wrong_answer_count' => $this->wrong_answer_count ?? 0,
            'quiz_duration' => $this->quiz->duration_per_question,
            'skipped_answer_count' => $this->skipped_answer_count ?? 0,
            'last_answered_at' => $this->last_answered_at,
            'obtained_mark' => $this->obtained_mark ?? 0,
        ];
    }
}
