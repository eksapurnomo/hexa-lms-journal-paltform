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
        Schema::table('notification_instances', function (Blueprint $table) {
            $table->unsignedBigInteger('recipient_id')->change();
            $table->dropColumn('recipient_id');
        });

        Schema::table('notification_instances', function (Blueprint $table) {
            $table->foreignId('recipient_id')->after('notification_id')->nullable()->constrained('users')->cascadeOnDelete();
            $table->foreignId('course_id')->after('recipient_id')->nullable()->constrained('courses')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('notification_instances', function (Blueprint $table) {
            $table->dropForeign(['course_id']);
            $table->dropColumn('course_id');

            // $table->dropForeign(['recipient_id']);
            // $table->dropColumn('recipient_id');

            // // Restore original recipient_id column
            // $table->foreignId('recipient_id')
            //     ->constrained('users')
            //     ->cascadeOnDelete();
        });
    }
};
