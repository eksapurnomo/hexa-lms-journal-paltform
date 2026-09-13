<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('journals', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('issn')->nullable();
            $table->string('eissn')->nullable();
            $table->string('status')->default('draft');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            // Use SET NULL so that if the admin user who created the journal is deleted, the journal isn't destroyed
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
        });

        Schema::create('journal_memberships', function (Blueprint $table) {
            $table->id();
            $table->foreignId('journal_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('role');
            $table->string('status')->default('active');
            $table->timestamps();

            $table->unique(['journal_id', 'user_id', 'role']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('journal_memberships');
        Schema::dropIfExists('journals');
    }
};
