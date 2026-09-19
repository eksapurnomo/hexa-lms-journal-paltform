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
        Schema::table('journal_membership_applications', function (Blueprint $table) {
            $table->string('recruitment_source')->nullable();
            $table->json('declarations')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('journal_membership_applications', function (Blueprint $table) {
            $table->dropColumn(['recruitment_source', 'declarations']);
        });
    }
};
