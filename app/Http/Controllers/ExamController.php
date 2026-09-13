<?php

namespace App\Http\Controllers;

use App\Enum\QuestionTypeEnum;
use App\Http\Requests\ExamSubmitRequest;
use App\Http\Resources\ExamSessionResource;
use App\Http\Resources\QuestionResource;
use App\Models\Exam;
use App\Models\ExamSession;
use App\Repositories\AnswerRepository;
use App\Repositories\ExamSessionRepository;
use App\Repositories\QuestionRepository;
use Illuminate\Support\Facades\Auth;

class ExamController extends Controller
{
    public function start(Exam $exam)
    {
        /** @var User */
        $loggedInUser = auth()->user();

        $isEnrolled = $exam->course->enrollments->contains('user_id', $loggedInUser->id);

        if (!$isEnrolled) {
            return $this->json('Cannot start exam without course enrollment', null, 403);
        }

        $eloquentQuery = ExamSessionRepository::query()->where('exam_id', $exam->id)
            ->where('course_id', $exam->course_id)
            ->where('user_id', $loggedInUser->id)
            ->where('submitted', true);

        $examSessionExists = (clone $eloquentQuery)->exists();

        if ($examSessionExists) {
            return $this->json('Exam session already submitted', null, 400);
        } else if (!$examSessionExists) {
            $examSessionTimeExists = (clone $eloquentQuery)->where('end_time', '>', now())->exists();

            if ($examSessionTimeExists) {
                return $this->json('You have already submitted the exam', null, 400);
            }
        }


        // Start the exam session
        $examSession = ExamSessionRepository::create([
            'course_id' => $exam->course->id,
            'exam_id' => $exam->id,
            'user_id' => $loggedInUser->id,
            'start_time' => now(),
            'end_time' => now()->addMinutes($exam->duration),
            'total_mark' => $exam->mark_per_question * $exam->questions->count(),
            'obtained_mark' => 0
        ]);

        $questions = QuestionRepository::query()->where('exam_id', '=', $exam->id)->get();

        return $this->json('Exam started successfully', [
            'examSession' => ExamSessionResource::make($examSession),
            'questions' => QuestionResource::collection($questions),
        ], 201);
    }

    public function submit(ExamSession $examSession, ExamSubmitRequest $request)
    {
        /** @var User */
        $loggedInUser =  Auth::guard('api')->user();

        if ($examSession->user_id !== $loggedInUser->id) {
            return $this->json('Cannot submit exam session', null, 403);
        }

        if ($examSession->submitted) {
            return $this->json('Exam session already submitted', null, 400);
        }

        if ($examSession->end_time <= now()) {
            return $this->json('Exam session already ended', null, 400);
        }

        // if (count($request->answers) !== $examSession->exam->questions->count()) {
        //     return $this->json('Invalid answer count', null, 400);
        // }

        $exam = $examSession->exam;
        $obtainedMark = 0;

        foreach ($request->answers as $answer) {
            $question = QuestionRepository::query()
                ->where('id', '=', $answer['question_id'])
                ->where('exam_id', '=', $exam->id)
                ->first();

            if (!$question) {
                return $this->json('Invalid question', null, 404);
            }

            if (in_array($question->question_type, QuestionTypeEnum::cases())) {
                return $this->json('Question type not supported', null, 400);
            }

            // Increment the obtained mark if the answer is correct
            AnswerRepository::storeAndEvaluate($question, $answer) ? $obtainedMark += $exam->mark_per_question : null;
        }

        ExamSessionRepository::update($examSession, [
            'submitted' => true,
            'obtained_mark' => $obtainedMark
        ]);

        return $this->json('Exam session submitted successfully', ExamSessionResource::make($examSession), 201);
    }
}
