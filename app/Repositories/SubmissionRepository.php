<?php

namespace App\Repositories;

use Abedin\Maker\Repositories\Repository;
use App\Models\Submission;
use Illuminate\Support\Facades\DB;

class SubmissionRepository extends Repository
{
    public static function model()
    {
        return Submission::class;
    }

    /**
     * Get a query builder scoped to the user's viewable submissions.
     */
    public static function getScopedQuery($user)
    {
        $query = self::query()->with(['journal', 'authors', 'files']);

        if ($user->hasRole('admin') || $user->is_admin) {
            return $query;
        }

        return $query->where(function ($q) use ($user) {
            // Author
            $q->where('created_by', $user->id)
              // Journal Owner (can see all in journal)
              ->orWhereHas('journal.memberships', function ($membershipQuery) use ($user) {
                  $membershipQuery->where('user_id', $user->id)
                                  ->where('role', 'owner')
                                  ->where('status', 'active');
              })
              // Assigned Editor (can see only assigned submissions in journal)
              ->orWhere(function ($editorQuery) use ($user) {
                  $editorQuery->where('editor_id', $user->id)
                              ->whereHas('journal.memberships', function ($membershipQuery) use ($user) {
                                  $membershipQuery->where('user_id', $user->id)
                                                  ->where('role', 'editor')
                                                  ->where('status', 'active');
                              });
              });
        });
    }

    public static function syncAuthors(Submission $submission, array $authorsData)
    {
        DB::transaction(function () use ($submission, $authorsData) {
            // Remove existing authors
            $submission->authors()->delete();

            // Create new ones with correct sequencing
            foreach ($authorsData as $index => $authorData) {
                $submission->authors()->create([
                    'user_id' => $authorData['user_id'] ?? null,
                    'first_name' => $authorData['first_name'],
                    'last_name' => $authorData['last_name'] ?? null,
                    'email' => $authorData['email'] ?? null,
                    'affiliation' => $authorData['affiliation'] ?? null,
                    'sequence' => $index + 1, // Enforce deterministic order
                    'is_corresponding' => $authorData['is_corresponding'] ?? false,
                ]);
            }
        });
    }
}
