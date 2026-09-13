<?php

use App\Models\Course;
use App\Models\Exam;
use App\Models\ExamSession;
use App\Models\Question;
use App\Models\Quiz;
use App\Models\QuizSession;
use App\Models\User;
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
        Schema::create('answers', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Course::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(User::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Exam::class)->nullable()->constrained('exams')->cascadeOnDelete();
            $table->foreignIdFor(ExamSession::class)->nullable()->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Quiz::class)->nullable()->constrained('quizzes')->cascadeOnDelete();
            $table->foreignIdFor(QuizSession::class)->nullable()->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Question::class)->constrained()->cascadeOnDelete();
            $table->boolean('is_correct');
            $table->string('answer_text');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('answers');
    }
};
