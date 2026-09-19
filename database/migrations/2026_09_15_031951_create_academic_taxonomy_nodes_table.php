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
        Schema::create('academic_taxonomy_nodes', function (Blueprint $table) {
            $table->id();
            $table->string('source'); // openalex, oecd, local
            $table->string('source_id');
            $table->foreignId('parent_id')->nullable()->constrained('academic_taxonomy_nodes')->nullOnDelete();
            $table->string('level'); // domain, field, subfield, topic
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->json('metadata')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['source', 'source_id'], 'taxonomy_source_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('academic_taxonomy_nodes');
    }
};
