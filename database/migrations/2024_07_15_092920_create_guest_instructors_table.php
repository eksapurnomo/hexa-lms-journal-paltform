<?php

use App\Models\Guest;
use App\Models\Instructor;
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
        Schema::create('guest_instructors', function (Blueprint $table) {
            $table->foreignIdFor(Guest::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Instructor::class)->constrained()->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('guest_instructors');
    }
};
