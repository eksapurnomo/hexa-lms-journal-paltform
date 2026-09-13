<?php

namespace App\Http\Controllers;

use App\Http\Requests\QuizSubmitRequest;
use App\Http\Resources\QuestionResource;
use App\Http\Resources\QuizSessionResource;
use App\Models\Quiz;
use App\Models\QuizSession;
use App\Repositories\AnswerRepository;
use App\Repositories\QuestionRepository;
use App\Repositories\QuizSessionRepository;
use Carbon\Carbon;

class QuizController extends Controller
{
    public function start(Quiz $quiz)
    {
        /** @var User */
        $loggedInUser = auth()->user();

        $isEnrolled = $quiz->course->enrollments->contains('user_id', $loggedInUser->id);

        if (!$isEnrolled) {
            return $this->json('Cannot start quiz without course enrollment', null, 403);
        }

        // $quizSessionExists = QuizSessionRepository::query()
        //     ->where('quiz_id', $quiz->id)
        //     ->where('course_id', $quiz->course_id)
        //     ->where('user_id', $loggedInUser->id)
        //     ->exists();

        // if ($quizSessionExists) {
        //     return $this->json('Quiz session already submitted', null, 400);
        // }

        $questions = QuestionRepository::query()->where('quiz_id', '=', $quiz->id)->get();
        $question = $questions->first();

        // Start the quiz session
        $quizSession = QuizSessionRepository::create([
            'course_id' => $quiz->course->id,
            'quiz_id' => $quiz->id,
            'user_id' => $loggedInUser->id,
            'seen_question_ids' => json_encode([$question->id]),
            'last_answered_at' => now(),
            'obtained_mark' => 0
        ]);

        return $this->json('Quiz started successfully', [
            'quiz_session' => QuizSessionResource::make($quizSession),
            'previous_was_correct' => null,
            'question' => QuestionResource::make($question),
            'question_count' => $questions->count()
        ], 201);
    }

    public function submit(QuizSession $quizSession, QuizSubmitRequest $request)
    {
        /** @var User */
        $loggedInUser = auth()->user();

        if ($quizSession->user_id !== $loggedInUser->id) {
            return $this->json('Cannot submit quiz answer', null, 403);
        }

        $quiz = $quizSession->quiz;
        $answer = $request->answer;

        $seenQuestionIds = json_decode($quizSession->seen_question_ids) ?? [];
        $answeredQuestionIds = json_decode($quizSession->answered_question_ids) ?? [];
        $question = QuestionRepository::query()
            ->where('id', '=', $answer['question_id'])
            ->where('quiz_id', '=', $quiz->id)
            ->first();


        if (
            !$question
            || in_array($question->id, $answeredQuestionIds)
            || !in_array($question->id, $seenQuestionIds)
        ) {
            return $this->json('Invalid question', null, 404);
        }

        $responseMessage = 'Answer submitted successfully';
        $isCorrect = false;

        if (now() > Carbon::parse($quizSession->last_answered_at)->addSeconds($quiz->duration_per_question)) {
            $answer['skip'] = true;
            $responseMessage = 'Answer skipped due to timeout';
        } else {
            $isCorrect = AnswerRepository::storeAndEvaluate($question, $answer);
        }

        // Get the next question
        $questions = QuestionRepository::query()->where('quiz_id', '=', $quiz->id)->get();
        $nextQuestion = $questions->whereNotIn('id', $seenQuestionIds)->isNotEmpty() ? $questions->whereNotIn('id', $seenQuestionIds)->random(1)->first() : null;

        // Update the quiz session state
        QuizSessionRepository::update($quizSession, [
            'seen_question_ids' => $nextQuestion ? json_encode(array_merge($seenQuestionIds, [$nextQuestion?->id])) : json_encode($seenQuestionIds),
            'answered_question_ids' => json_encode(array_merge($answeredQuestionIds, [$question->id])),
            'obtained_mark' => !$answer['skip'] && $isCorrect ? $quizSession->obtained_mark + $quiz->mark_per_question : $quizSession->obtained_mark,
            'right_answer_count' => !$answer['skip'] && $isCorrect ? $quizSession->right_answer_count + 1 : $quizSession->right_answer_count,
            'wrong_answer_count' => !$answer['skip'] && !$isCorrect ? $quizSession->wrong_answer_count + 1 : $quizSession->wrong_answer_count,
            'skipped_answer_count' => $answer['skip'] ? $quizSession->skipped_answer_count + 1 : $quizSession->skipped_answer_count,
            'last_answered_at' => now(),
        ]);

        return $this->json($responseMessage, [
            'quiz_session' => QuizSessionResource::make($quizSession),
            'previous_was_correct' => $isCorrect,
            'question' => $nextQuestion ? QuestionResource::make($nextQuestion) : null
        ], 201);
    }
}
