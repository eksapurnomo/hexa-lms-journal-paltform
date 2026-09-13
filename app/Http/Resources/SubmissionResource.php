<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SubmissionResource extends JsonResource
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
            'journal_id' => $this->journal_id,
            'created_by' => $this->created_by,
            'title' => $this->title,
            'abstract' => $this->abstract,
            'keywords' => $this->keywords,
            'status' => $this->status,
            'submitted_at' => $this->submitted_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'authors' => SubmissionAuthorResource::collection($this->whenLoaded('authors')),
            'revisions' => $this->whenLoaded('revisions', function () {
                return $this->revisions->map(function ($rev) {
                    return [
                        'id' => $rev->id,
                        'version_number' => $rev->version_number,
                        'created_at' => $rev->created_at,
                        'files' => $rev->relationLoaded('files') ? SubmissionFileResource::collection($rev->files) : [],
                        // We intentionally do NOT expose review_rounds or editorial decisions here to authors
                    ];
                });
            }),
        ];
    }
}
