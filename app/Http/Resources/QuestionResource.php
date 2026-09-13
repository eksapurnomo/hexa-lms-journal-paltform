<?php

namespace App\Http\Resources;

use App\Enum\QuestionTypeEnum;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QuestionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $normalizedOptions = [];

        // Normalize options response format (to match frontend)
        foreach (json_decode($this->options) as $option => $value) {
            $normalizedOptions[] = [
                'text' => $this->question_type === QuestionTypeEnum::BINARY->value ? $option : $value->text,
                'is_correct' => $value->is_correct
            ];
        }

        return [
            'id' => $this->id,
            'question_text' => $this->question_text,
            'question_type' => $this->question_type,
            'options' => $normalizedOptions
        ];
    }
}
