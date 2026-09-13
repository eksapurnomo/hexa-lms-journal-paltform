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
        Schema::create('reviewer_applications', function (Blueprint $table) {
            $table->id();
            
            // Core identity and targeting
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('journal_id')->constrained('journals')->onDelete('cascade');
            $table->string('status')->default('pending'); // pending, accepted, denied

            // Personal / Academic
            $table->string('affiliation');
            $table->string('department');
            $table->string('academic_position');
            
            // Academic Profile
            $table->string('orcid')->nullable();
            $table->string('academic_url')->nullable();
            
            // Expertise
            $table->string('primary_research_area');
            $table->text('research_keywords')->nullable();
            $table->text('expertise')->nullable();
            
            // Experience
            $table->unsignedSmallInteger('years_of_experience')->default(0);
            $table->text('previous_experience')->nullable();
            
            // Availability
            $table->boolean('available_for_review')->default(true);
            $table->unsignedSmallInteger('max_reviews_per_month')->default(1);
            
            // Agreements
            $table->boolean('agreed_confidentiality')->default(false);
            $table->boolean('agreed_conflict_of_interest')->default(false);
            $table->boolean('agreed_guidelines')->default(false);
            
            // Editorial Review Fields
            $table->text('denial_reason')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
            
            // Prevent exactly duplicated pending applications for the same user + journal
            $table->unique(['user_id', 'journal_id', 'status'], 'user_journal_status_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reviewer_applications');
    }
};
