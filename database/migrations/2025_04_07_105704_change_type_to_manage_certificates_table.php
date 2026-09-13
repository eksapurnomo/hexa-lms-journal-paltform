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
        Schema::table('manage_certificates', function (Blueprint $table) {
            if (Schema::hasColumn('manage_certificates', 'frame')) {
                $table->dropColumn('frame');
            }
            $table->foreignId('frame_id')->nullable()->after('id')->constrained('frames')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('manage_certificates', function (Blueprint $table) {
            // $table->unsignedBigInteger('frame_id')->nullable()->change();
            // $table->dropColumn('frame_id');
            $table->dropForeign(['frame_id']);
            $table->dropColumn('frame_id');

            // Re-add the old column if necessary
            $table->integer('frame')->nullable()->after('id');
        });
    }
};
