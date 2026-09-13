<?php

use App\Models\Media;
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
        Schema::create('manage_certificates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_logo_id')->nullable()->constrained('media')->nullOnDelete();
            $table->foreignId('subsite_logo_id')->nullable()->constrained('media')->nullOnDelete();
            $table->foreignId('author_signature_id')->nullable()->constrained('media')->nullOnDelete();
            $table->string('certificate_title')->nullable();
            $table->text('certificate_short_text')->nullable();
            $table->longText('certificate_text')->nullable();
            $table->string('author_name')->nullable();
            $table->string('author_designation')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('manage_certificates');
    }
};
