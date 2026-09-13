<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Alter enum to string for flexibility using raw SQL to avoid doctrine/dbal issues
        DB::statement("ALTER TABLE review_assignments MODIFY review_mode VARCHAR(255) DEFAULT 'double_blind'");

        // Migrate legacy 'blind' to 'single_blind'
        DB::table('review_assignments')->where('review_mode', 'blind')->update(['review_mode' => 'single_blind']);
    }

    public function down(): void
    {
        // Revert legacy 'single_blind' back to 'blind'
        DB::table('review_assignments')->where('review_mode', 'single_blind')->update(['review_mode' => 'blind']);
        
        // Note: Reverting a string back to an enum can be problematic in MariaDB if there are data mismatches.
        // We will leave it as string in down() or just suppress it.
    }
};
