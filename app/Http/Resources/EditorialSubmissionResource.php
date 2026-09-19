<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EditorialSubmissionResource extends JsonResource
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
            'editor_id' => $this->editor_id,
            'created_by' => $this->created_by,
            'title' => $this->title,
            'abstract' => $this->abstract,
            'keywords' => $this->keywords,
            'status' => $this->status,
            'submitted_at' => $this->submitted_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'journal' => $this->whenLoaded('journal'),
            'authors' => SubmissionAuthorResource::collection($this->whenLoaded('authors')),
            'revisions' => $this->whenLoaded('revisions', function () {
                return $this->revisions->map(function ($rev) {
                    return [
                        'id'             => $rev->id,
                        'version_number' => $rev->version_number,
                        'created_at'     => $rev->created_at,
                        'files'          => $rev->relationLoaded('files')
                            ? SubmissionFileResource::collection($rev->files)
                            : [],
                        'review_rounds' => $rev->relationLoaded('reviewRounds')
                            ? $rev->reviewRounds->map(function ($round) {
                                return [
                                    'id'          => $round->id,
                                    'round_number' => $round->round_number,
                                    'review_model' => $round->review_model,
                                    'minimum_reviewers' => $round->minimum_reviewers,
                                    'target_reviewers' => $round->target_reviewers,
                                    'maximum_reviewers' => $round->maximum_reviewers,
                                    'created_at'  => $round->created_at,
                                    'editorial_decision' => $round->relationLoaded('editorialDecision')
                                        ? ($round->editorialDecision ? [
                                            'id'         => $round->editorialDecision->id,
                                            'decision'   => $round->editorialDecision->decision,
                                            'comments'   => $round->editorialDecision->comments,
                                            'created_at' => $round->editorialDecision->created_at,
                                        ] : null)
                                        : null,
                                    'assignments' => $round->relationLoaded('assignments')
                                        ? $round->assignments->map(function ($assignment) {
                                            return [
                                                'id'          => $assignment->id,
                                                'reviewer_id' => $assignment->reviewer_id,
                                                'reviewer'    => $assignment->relationLoaded('reviewer') && $assignment->reviewer ? [
                                                    'id'    => $assignment->reviewer->id,
                                                    'name'  => $assignment->reviewer->name,
                                                    'email' => $assignment->reviewer->email,
                                                ] : null,
                                                'status'      => $assignment->status,
                                                'review_mode' => $assignment->review_mode,
                                                'assigned_at' => $assignment->assigned_at,
                                                'accepted_at' => $assignment->accepted_at,
                                                'peer_review' => $assignment->relationLoaded('peerReview')
                                                    ? ($assignment->peerReview ? [
                                                        'id'                  => $assignment->peerReview->id,
                                                        'recommendation'      => $assignment->peerReview->recommendation,
                                                        'comments_to_editor'  => $assignment->peerReview->comments_to_editor,
                                                        'comments_to_author'  => $assignment->peerReview->comments_to_author,
                                                        'submitted_at'        => $assignment->peerReview->submitted_at,
                                                    ] : null)
                                                    : null,
                                            ];
                                        })->values()
                                        : [],
                                ];
                            })->values()
                            : [],
                    ];
                });
            }),
            'editor' => $this->whenLoaded('editor', function () {
                return [
                    'id' => $this->editor->id,
                    'name' => $this->editor->name,
                    'email' => $this->editor->email,
                ];
            }),
            'editorial_events' => $this->whenLoaded('editorialEvents', function () {
                return $this->editorialEvents->map(function ($event) {
                    return [
                        'id' => $event->id,
                        'action' => $event->action,
                        'payload' => $event->payload,
                        'created_at' => $event->created_at,
                        'actor' => $event->user ? [
                            'id' => $event->user->id,
                            'name' => $event->user->name,
                        ] : null,
                    ];
                });
            }),
        ];
    }
}
