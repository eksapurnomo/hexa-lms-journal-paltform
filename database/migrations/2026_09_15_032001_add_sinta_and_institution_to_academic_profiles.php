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
        Schema::table('academic_profiles', function (Blueprint $table) {
            $table->string('sinta_id')->nullable()->after('scopus_author_id');
            $table->foreignId('institution_id')->nullable()->constrained('institutions')->nullOnDelete()->after('institution');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('academic_profiles', function (Blueprint $table) {
            $table->dropForeign(['institution_id']);
            $table->dropColumn(['sinta_id', 'institution_id']);
        });
    }
};
