<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Journal;
use App\Models\JournalMembership;
use App\Models\JournalMembershipApplication;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JournalUserProcessMonitorTest extends TestCase
{
    use RefreshDatabase;

    private function makeJournal($title = 'Test Journal'): Journal
    {
        return Journal::create([
            'title'  => $title,
            'slug'   => \Illuminate\Support\Str::uuid(),
            'status' => 'active',
        ]);
    }

    private function makeAdmin(): User
    {
        return User::factory()->create(['is_admin' => true]);
    }

    public function test_admin_can_access_monitor_list()
    {
        $admin = $this->makeAdmin();
        $journal = $this->makeJournal();

        $response = $this->actingAs($admin)->get(route('admin.journal.process-flow.users.index', ['journal_id' => $journal->id]));
        $response->assertStatus(200);
        $response->assertSee($journal->title);
    }

    public function test_unauthorized_user_gets_403()
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)->get(route('admin.journal.process-flow.users.index'));
        $response->assertStatus(403);
    }

    public function test_journal_owner_isolation_and_idor()
    {
        $journalA = $this->makeJournal('Journal A');
        $journalB = $this->makeJournal('Journal B');

        $ownerA = User::factory()->create();
        JournalMembership::create(['user_id' => $ownerA->id, 'journal_id' => $journalA->id, 'role' => 'owner', 'status' => 'active']);

        // Access Journal A
        $responseA = $this->actingAs($ownerA)->get(route('admin.journal.process-flow.users.index', ['journal_id' => $journalA->id]));
        $responseA->assertStatus(200);

        // Access Journal B
        $responseB = $this->actingAs($ownerA)->get(route('admin.journal.process-flow.users.index', ['journal_id' => $journalB->id]));
        $responseB->assertStatus(403);
    }

    public function test_detail_view_loads_for_authorized_journal()
    {
        $admin = $this->makeAdmin();
        $journal = $this->makeJournal();

        $user = User::factory()->create();
        JournalMembership::create(['user_id' => $user->id, 'journal_id' => $journal->id, 'role' => 'editor', 'status' => 'active']);

        $response = $this->actingAs($admin)->get(route('admin.journal.process-flow.users.show', ['journal' => $journal->id, 'user' => $user->id]));
        $response->assertStatus(200);
        $response->assertSee('Process Flow');
        $response->assertSee('Editorial Access');
    }

    public function test_pending_verification_detected()
    {
        $admin = $this->makeAdmin();
        $journal = $this->makeJournal();

        $user = User::factory()->create();
        JournalMembershipApplication::create(['user_id' => $user->id, 'journal_id' => $journal->id, 'requested_role' => 'editor', 'status' => 'needs_revision']);

        $response = $this->actingAs($admin)->get(route('admin.journal.process-flow.users.show', ['journal' => $journal->id, 'user' => $user->id]));
        $response->assertStatus(200);
        $response->assertSee('Needs Action');
        $response->assertSee('Membership application requires revision');
    }

    public function test_demo_seeder_idempotency()
    {
        $this->artisan('db:seed', ['--class' => 'JournalProcessFlowDemoSeeder'])->assertExitCode(0);
        
        $countUsers = User::where('email', 'like', 'jpf.%')->count();
        $this->assertEquals(4, $countUsers);

        // Run again
        $this->artisan('db:seed', ['--class' => 'JournalProcessFlowDemoSeeder'])->assertExitCode(0);
        
        // Count should not duplicate
        $countUsers = User::where('email', 'like', 'jpf.%')->count();
        $this->assertEquals(4, $countUsers);
    }
}
