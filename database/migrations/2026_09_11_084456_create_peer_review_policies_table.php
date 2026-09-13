<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('peer_review_policies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('journal_id')->constrained()->cascadeOnDelete();
            $table->string('review_model')->default('double_blind');
            $table->integer('minimum_reviewers')->default(2);
            $table->integer('target_reviewers')->default(2);
            $table->integer('maximum_reviewers')->default(4);
            $table->boolean('reviewer_agreement_required')->default(false);
            $table->boolean('conflict_of_interest_required')->default(false);
            $table->boolean('confidentiality_required')->default(false);
            $table->timestamps();

            $table->unique('journal_id');
        });

        // Seed default policy for existing journals
        $journals = DB::table('journals')->get();
        foreach ($journals as $journal) {
            DB::table('peer_review_policies')->insert([
                'journal_id' => $journal->id,
                'review_model' => 'double_blind',
                'minimum_reviewers' => 2,
                'target_reviewers' => 2,
                'maximum_reviewers' => 4,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('peer_review_policies');
    }
};
