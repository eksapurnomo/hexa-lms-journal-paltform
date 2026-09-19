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
        Schema::create('academic_profile_expertise', function (Blueprint $table) {
            $table->id();
            $table->foreignId('academic_profile_id')->constrained('academic_profiles')->onDelete('cascade');
            $table->foreignId('taxonomy_node_id')->constrained('academic_taxonomy_nodes')->onDelete('cascade');
            $table->timestamps();

            $table->unique(['academic_profile_id', 'taxonomy_node_id'], 'profile_expertise_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('academic_profile_expertise');
    }
};
