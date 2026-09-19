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
        Schema::create('journal_subjects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('journal_id')->constrained('journals')->onDelete('cascade');
            $table->foreignId('taxonomy_node_id')->constrained('academic_taxonomy_nodes')->onDelete('cascade');
            $table->string('type'); // primary, secondary, topic
            $table->timestamps();

            $table->unique(['journal_id', 'taxonomy_node_id', 'type'], 'journal_subject_type_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('journal_subjects');
    }
};
