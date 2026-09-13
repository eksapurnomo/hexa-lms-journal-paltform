<?php

namespace Tests\Feature;

use App\Models\Journal;
use App\Models\JournalMembership;
use App\Models\Submission;
use App\Models\User;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EditorialDeskDiagnosticTest extends TestCase
{
    use RefreshDatabase;

    public function test_editorial_desk_diagnostic()
    {
        // 1. Create a Journal
        $journal = Journal::create([
            'title' => 'Diagnostic Journal',
            'slug' => 'diagnostic-journal',
            'description' => 'Test',
        ]);

        // 2. Create Users
        $admin = User::factory()->create(['is_admin' => 1]);
        
        $author = User::factory()->create();
        
        $journalOwner = User::factory()->create();
        JournalMembership::create([
            'journal_id' => $journal->id,
            'user_id' => $journalOwner->id,
            'role' => 'owner',
            'status' => 'active',
        ]);
        
        $assignedEditor = User::factory()->create();
        JournalMembership::create([
            'journal_id' => $journal->id,
            'user_id' => $assignedEditor->id,
            'role' => 'editor',
            'status' => 'active',
        ]);

        $ordinaryUser = User::factory()->create();

        // 3. Create the Diagnostic Submission
        $submission = new Submission();
        $submission->forceFill([
            'journal_id' => $journal->id,
            'created_by' => $author->id,
            'editor_id' => $assignedEditor->id,
            'title' => '[DIAGNOSTIC] HexaLMS Editorial Desk Test Submission',
            'status' => Submission::STATUS_SUBMITTED,
        ])->save();

        // Need to create authors relation for the SubmissionRepository::getScopedQuery with => authors
        $submission->authors()->create([
            'user_id' => $author->id,
            'first_name' => 'John',
            'sequence' => 1,
            'is_corresponding' => true,
        ]);

        // 4. Test API Contexts
        $contexts = [
            'Admin/editorial' => $admin,
            'Journal Owner' => $journalOwner,
            'Assigned Editor' => $assignedEditor,
            'Author' => $author,
            'Ordinary user' => $ordinaryUser,
        ];

        $results = [];

        foreach ($contexts as $name => $user) {
            $response = $this->actingAs($user)->getJson('/api/editorial/submissions');
            
            $results[$name] = [
                'status' => $response->status(),
                'data' => $response->status() === 200 ? $response->json('data') : null,
            ];
        }

        dump($results);
        
        $this->assertTrue(true);
    }
}
