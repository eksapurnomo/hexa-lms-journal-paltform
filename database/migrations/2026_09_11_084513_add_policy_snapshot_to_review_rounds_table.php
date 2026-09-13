<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('review_rounds', function (Blueprint $table) {
            $table->string('review_model')->default('double_blind')->after('round_number');
            $table->integer('minimum_reviewers')->default(2)->after('review_model');
            $table->integer('target_reviewers')->default(2)->after('minimum_reviewers');
            $table->integer('maximum_reviewers')->default(4)->after('target_reviewers');
        });
    }

    public function down(): void
    {
        Schema::table('review_rounds', function (Blueprint $table) {
            $table->dropColumn(['review_model', 'minimum_reviewers', 'target_reviewers', 'maximum_reviewers']);
        });
    }
};
