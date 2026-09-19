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
        Schema::create('academic_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->onDelete('cascade');
            $table->string('highest_degree')->nullable();
            $table->string('academic_position')->nullable();
            $table->string('institution')->nullable();
            $table->string('department')->nullable();
            $table->string('country')->nullable();
            $table->text('biography')->nullable();
            $table->text('research_interests')->nullable();
            $table->json('expertise')->nullable();
            $table->string('orcid')->nullable();
            $table->string('scopus_author_id')->nullable();
            $table->string('google_scholar_url')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('academic_profiles');
    }
};
