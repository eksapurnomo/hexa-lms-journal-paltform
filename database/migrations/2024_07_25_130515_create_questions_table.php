<?php

use App\Models\Course;
use App\Models\Exam;
use App\Models\Quiz;
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
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Course::class)->constrained('courses')->cascadeOnDelete();
            $table->foreignIdFor(Exam::class)->nullable()->constrained('exams')->cascadeOnDelete();
            $table->foreignIdFor(Quiz::class)->nullable()->constrained('quizzes')->cascadeOnDelete();
            $table->string('question_text');
            $table->string('question_type');
            $table->json('options');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('questions');
    }
};
