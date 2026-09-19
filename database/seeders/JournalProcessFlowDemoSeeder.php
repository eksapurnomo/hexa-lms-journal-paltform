<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Journal;
use App\Models\JournalMembershipApplication;
use App\Models\VerificationEvidence;
use App\Models\JournalMembership;
use App\Models\Submission;
use App\Models\SubmissionRevision;
use App\Models\SubmissionEditorialEvent;
use App\Models\ReviewRound;
use App\Models\ReviewAssignment;
use App\Models\PeerReview;
use App\Models\EditorialDecision;
use App\Models\AcademicProfile;
use Carbon\Carbon;
use Illuminate\Support\Str;

class JournalProcessFlowDemoSeeder extends Seeder
{
    public function run()
    {
        // Create Demo Admin
        $demoAdmin = User::firstOrCreate(
            ['email' => 'admin@readylms.local'],
            [
                'name' => 'Platform Admin',
                'password' => bcrypt('password'),
                'is_admin' => true,
            ]
        );

        // Create Demo User
        $demoEditor = User::firstOrCreate(
            ['email' => 'jpf.editor@demo.readylms.local'],
            [
                'name' => 'JPF Demo Editor',
                'password' => bcrypt('password'),
            ]
        );

        AcademicProfile::firstOrCreate(
            ['user_id' => $demoEditor->id],
            [
                'institution' => 'Demo University',
                'department' => 'Demo Sciences',
                'biography' => 'Demo user for JPF process.',
            ]
        );

        // Create Demo Reviewers
        $demoReviewer1 = User::firstOrCreate(
            ['email' => 'jpf.reviewer1@demo.readylms.local'],
            [
                'name' => 'JPF Demo Reviewer 1',
                'password' => bcrypt('password'),
            ]
        );
        $demoReviewer2 = User::firstOrCreate(
            ['email' => 'jpf.reviewer2@demo.readylms.local'],
            [
                'name' => 'JPF Demo Reviewer 2',
                'password' => bcrypt('password'),
            ]
        );

        // Create Demo Author
        $demoAuthor = User::firstOrCreate(
            ['email' => 'jpf.author@demo.readylms.local'],
            [
                'name' => 'JPF Demo Author',
                'password' => bcrypt('password'),
            ]
        );

        // Create Demo Journal
        $demoJournal = Journal::firstOrCreate(
            ['slug' => 'jpf-demo-journal'],
            [
                'title' => 'JPF Demo Journal',
                'description' => 'A journal specifically for demoing the process flow.',
                'status' => 'active',
            ]
        );

        // Editor Application & Membership
        $app = JournalMembershipApplication::firstOrCreate(
            [
                'user_id' => $demoEditor->id,
                'journal_id' => $demoJournal->id,
                'requested_role' => 'editor',
            ],
            [
                'status' => 'approved',
                'submitted_at' => Carbon::now()->subDays(10),
                'reviewed_at' => Carbon::now()->subDays(9),
                'reviewed_by' => $demoAdmin->id,
            ]
        );

        VerificationEvidence::firstOrCreate(
            ['journal_membership_application_id' => $app->id],
            [
                'user_id' => $demoEditor->id,
                'category' => 'identity',
                'file_path' => 'demo/fake-evidence.pdf',
            ]
        );

        JournalMembership::firstOrCreate(
            [
                'user_id' => $demoEditor->id,
                'journal_id' => $demoJournal->id,
                'role' => 'editor',
            ],
            [
                'status' => 'active',
            ]
        );

        // Reviewers Memberships
        JournalMembership::firstOrCreate(
            ['user_id' => $demoReviewer1->id, 'journal_id' => $demoJournal->id, 'role' => 'reviewer'],
            ['status' => 'active']
        );
        JournalMembership::firstOrCreate(
            ['user_id' => $demoReviewer2->id, 'journal_id' => $demoJournal->id, 'role' => 'reviewer'],
            ['status' => 'active']
        );

        // Submission
        $submission = Submission::firstOrCreate(
            [
                'journal_id' => $demoJournal->id,
                'title' => 'Demo Submission for Process Flow',
            ],
            [
                'created_by' => $demoAuthor->id,
                'editor_id' => $demoEditor->id,
                'status' => 'accepted',
                'abstract' => 'This is a demo submission.',
                'submitted_at' => Carbon::now()->subDays(8),
            ]
        );

        $revision = SubmissionRevision::firstOrCreate(
            [
                'submission_id' => $submission->id,
                'version_number' => 1,
            ]
        );

        // Review Round
        $round = ReviewRound::firstOrCreate(
            [
                'submission_revision_id' => $revision->id,
                'round_number' => 1,
            ],
            [
                'minimum_reviewers' => 2,
                'target_reviewers' => 2,
                'maximum_reviewers' => 3,
                'review_model' => 'double_blind',
            ]
        );

        // Review Assignments
        $assignment1 = ReviewAssignment::firstOrCreate(
            [
                'review_round_id' => $round->id,
                'reviewer_id' => $demoReviewer1->id,
            ],
            [
                'assigned_by' => $demoEditor->id,
                'status' => 'submitted',
                'review_mode' => 'double_blind',
                'assigned_at' => Carbon::now()->subDays(7),
            ]
        );

        $assignment2 = ReviewAssignment::firstOrCreate(
            [
                'review_round_id' => $round->id,
                'reviewer_id' => $demoReviewer2->id,
            ],
            [
                'assigned_by' => $demoEditor->id,
                'status' => 'submitted',
                'review_mode' => 'double_blind',
                'assigned_at' => Carbon::now()->subDays(7),
            ]
        );

        // Peer Reviews
        PeerReview::firstOrCreate(
            ['review_assignment_id' => $assignment1->id],
            [
                'recommendation' => 'accept',
                'comments_to_author' => 'Looks great.',
                'comments_to_editor' => 'Ready to go.',
                'submitted_at' => Carbon::now()->subDays(5),
            ]
        );

        PeerReview::firstOrCreate(
            ['review_assignment_id' => $assignment2->id],
            [
                'recommendation' => 'revisions',
                'comments_to_author' => 'Minor changes needed.',
                'comments_to_editor' => 'Almost ready.',
                'submitted_at' => Carbon::now()->subDays(4),
            ]
        );

        // Editorial Decision
        EditorialDecision::firstOrCreate(
            ['review_round_id' => $round->id],
            [
                'user_id' => $demoEditor->id,
                'decision' => 'accept',
                'comments' => 'Accepted for demo.',
            ]
        );

        $this->command->info('JPF.3.2 Demo Scenario Seeded.');
        $this->command->warn('NOTE: Publication domain is not currently implemented. Scenario halted at EditorialDecision = Accepted.');
    }
}
