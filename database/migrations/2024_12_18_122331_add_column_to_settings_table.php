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
        Schema::table('settings', function (Blueprint $table) {
            $table->foreignId('scaner_id')->nullable()->constrained('media')->nullOnDelete();
            $table->string('footer_contact_number')->nullable();
            $table->string('footer_support_mail')->nullable();
            $table->longText('footer_description')->nullable();
            $table->string('play_store_url')->nullable();
            $table->string('app_store_url')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropConstrainedForeignId('scaner_id');
            $table->dropColumn('footer_contact_number');
            $table->dropColumn('footer_support_mail');
            $table->dropColumn('footer_description');
            $table->dropColumn('play_store_url');
            $table->dropColumn('app_store_url');
        });
    }
};
