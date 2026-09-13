<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Create target schema
        Schema::create('submission_revisions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('submission_id')->constrained('submissions')->cascadeOnDelete();
            $table->integer('version_number')->default(1);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('review_rounds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('submission_revision_id')->constrained('submission_revisions')->cascadeOnDelete();
            $table->integer('round_number')->default(1);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('review_criteria', function (Blueprint $table) {
            $table->id();
            $table->foreignId('journal_id')->constrained('journals')->cascadeOnDelete();
            $table->string('name');
            $table->string('type')->default('rating'); // rating, text, boolean
            $table->json('config')->nullable(); // e.g. min, max, options
            $table->string('status')->default('active'); // active, retired
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('review_criterion_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('peer_review_id')->constrained('peer_reviews')->cascadeOnDelete();
            $table->foreignId('review_criterion_id')->constrained('review_criteria')->restrictOnDelete();
            $table->text('response');
            $table->timestamps();
        });

        Schema::create('editorial_decisions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('review_round_id')->constrained('review_rounds')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete(); // The editor making the decision
            $table->string('decision'); // e.g., accept, reject, revision_required
            $table->text('comments')->nullable();
            $table->timestamps();
        });

        Schema::create('administrative_override_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete(); // Admin
            $table->string('target_type'); // e.g., EditorialDecision
            $table->unsignedBigInteger('target_id');
            $table->text('reason');
            $table->json('payload')->nullable();
            $table->timestamps();
        });

        // 2. Add target foreign keys as nullable temporarily
        Schema::table('submission_files', function (Blueprint $table) {
            $table->foreignId('submission_revision_id')->nullable()->after('submission_id')->constrained('submission_revisions')->cascadeOnDelete();
        });

        Schema::table('review_assignments', function (Blueprint $table) {
            $table->foreignId('review_round_id')->nullable()->after('submission_id')->constrained('review_rounds')->cascadeOnDelete();
        });

        // 3. Migrate Legacy Data
        $submissions = DB::table('submissions')->get();

        foreach ($submissions as $sub) {
            // Create Revision v1 for ALL submissions to ensure no files are orphaned
            $revId = DB::table('submission_revisions')->insertGetId([
                'submission_id' => $sub->id,
                'version_number' => 1,
                'created_at' => $sub->created_at ?? now(),
                'updated_at' => $sub->updated_at ?? now(),
            ]);

            // Re-parent files
            DB::table('submission_files')
                ->where('submission_id', $sub->id)
                ->update(['submission_revision_id' => $revId]);

            // Check if there are assignments
            $assignments = DB::table('review_assignments')
                ->where('submission_id', $sub->id)
                ->get();

            if ($assignments->isNotEmpty()) {
                // Create Round 1
                $roundId = DB::table('review_rounds')->insertGetId([
                    'submission_revision_id' => $revId,
                    'round_number' => 1,
                    'created_at' => $assignments->first()->created_at ?? now(),
                    'updated_at' => $assignments->first()->updated_at ?? now(),
                ]);

                // Re-parent assignments
                DB::table('review_assignments')
                    ->where('submission_id', $sub->id)
                    ->update(['review_round_id' => $roundId]);
            }
        }

        // Now we can safely make submission_revision_id non-nullable.
        // Clean up orphaned review_assignments if any (should not be any since we covered all submissions, but just in case)
        DB::table('review_assignments')->whereNull('review_round_id')->delete(); 

        Schema::table('submission_files', function (Blueprint $table) {
            $table->dropForeign(['submission_id']);
            $table->dropColumn('submission_id');
        });
        
        Schema::table('submission_files', function (Blueprint $table) {
            $table->foreignId('submission_revision_id')->nullable(false)->change();
        });

        Schema::table('review_assignments', function (Blueprint $table) {
            $table->dropForeign(['submission_id']);
            $table->dropColumn('submission_id');
        });

        Schema::table('review_assignments', function (Blueprint $table) {
            $table->foreignId('review_round_id')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('review_assignments', function (Blueprint $table) {
            $table->foreignId('submission_id')->nullable()->after('review_round_id')->constrained('submissions')->cascadeOnDelete();
        });

        $assignments = DB::table('review_assignments')
            ->join('review_rounds', 'review_assignments.review_round_id', '=', 'review_rounds.id')
            ->join('submission_revisions', 'review_rounds.submission_revision_id', '=', 'submission_revisions.id')
            ->select('review_assignments.id', 'submission_revisions.submission_id')
            ->get();
        foreach ($assignments as $a) {
            DB::table('review_assignments')->where('id', $a->id)->update(['submission_id' => $a->submission_id]);
        }

        Schema::table('review_assignments', function (Blueprint $table) {
            $table->dropForeign(['review_round_id']);
            $table->dropColumn('review_round_id');
        });

        Schema::table('submission_files', function (Blueprint $table) {
            $table->foreignId('submission_id')->nullable()->after('submission_revision_id')->constrained('submissions')->cascadeOnDelete();
        });

        $files = DB::table('submission_files')
            ->join('submission_revisions', 'submission_files.submission_revision_id', '=', 'submission_revisions.id')
            ->select('submission_files.id', 'submission_revisions.submission_id')
            ->get();
        foreach ($files as $f) {
            DB::table('submission_files')->where('id', $f->id)->update(['submission_id' => $f->submission_id]);
        }

        Schema::table('submission_files', function (Blueprint $table) {
            $table->dropForeign(['submission_revision_id']);
            $table->dropColumn('submission_revision_id');
        });

        Schema::dropIfExists('administrative_override_events');
        Schema::dropIfExists('editorial_decisions');
        Schema::dropIfExists('review_criterion_responses');
        Schema::dropIfExists('review_criteria');
        Schema::dropIfExists('review_rounds');
        Schema::dropIfExists('submission_revisions');
    }
};
