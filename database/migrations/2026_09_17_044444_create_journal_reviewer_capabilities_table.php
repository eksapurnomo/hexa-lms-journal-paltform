<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('journal_reviewer_capabilities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('journal_membership_id')->unique()->constrained()->cascadeOnDelete();
            $table->boolean('available_for_review')->default(true);
            $table->integer('max_reviews_per_month')->default(2);
            $table->integer('years_of_experience')->nullable();
            $table->text('previous_experience')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('journal_reviewer_capabilities');
    }
};
