<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReviewerAssignmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $mode = $this->review_mode;
        
        // Eager-loaded relations checking
        $round = $this->relationLoaded('reviewRound') ? $this->reviewRound : null;
        $revision = $round && $round->relationLoaded('submissionRevision') ? $round->submissionRevision : null;
        $submission = $revision && $revision->relationLoaded('submission') ? $revision->submission : null;

        return [
            'id' => $this->id,
            'status' => $this->status,
            'review_mode' => $mode,
            'assigned_at' => $this->assigned_at,
            'accepted_at' => $this->accepted_at,
            'declined_at' => $this->declined_at,
            'cancelled_at' => $this->cancelled_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            
            'peer_review' => $this->whenLoaded('peerReview', function () {
                $pr = $this->peerReview;
                if (!$pr) return null;
                return [
                    'id'                  => $pr->id,
                    'recommendation'      => $pr->recommendation,
                    'comments_to_editor'  => $pr->comments_to_editor,
                    'comments_to_author'  => $pr->comments_to_author,
                    'submitted_at'        => $pr->submitted_at,
                    'criterion_responses' => $pr->relationLoaded('criterionResponses')
                        ? $pr->criterionResponses->map(fn ($r) => [
                            'id'                  => $r->id,
                            'review_criterion_id' => $r->review_criterion_id,
                            'response'            => $r->response,
                        ])->values()
                        : [],
                ];
            }),
            'round_number' => $round ? $round->round_number : null,

            'submission' => $submission ? array_merge([
                'id' => $submission->id,
                'title' => $submission->title,
                'abstract' => $submission->abstract,
                'keywords' => $submission->keywords,
                'journal_id' => $submission->journal_id,
                'status' => $submission->status,
                'submitted_at' => $submission->submitted_at,
            ], 
            (in_array($mode, ['open', 'single_blind']) || ($submission && \Illuminate\Support\Facades\Gate::allows('editorial-process', [$submission]))) ? [
                'created_by' => $submission->created_by,
                'authors' => $submission->relationLoaded('authors') 
                    ? SubmissionAuthorResource::collection($submission->authors) 
                    : [],
            ] : []) : null,
            
            'files' => $revision && $revision->relationLoaded('files') 
                ? SubmissionFileResource::collection($revision->files) 
                : null,
        ];
    }
}
