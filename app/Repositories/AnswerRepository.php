<?php

namespace App\Repositories;

use Abedin\Maker\Repositories\Repository;
use App\Enum\QuestionTypeEnum;
use App\Models\Answer;
use App\Models\Question;

class AnswerRepository extends Repository
{
    public static function model()
    {
        return Answer::class;
    }

    public static function storeAndEvaluate(Question $question, $answer): bool
    {
        $options = json_decode($question->options, true);
        $isCorrect = false;

        switch ($question->question_type) {
            case QuestionTypeEnum::BINARY->value:
                if (isset($options[$answer['choice']]) && $options[$answer['choice']]['is_correct']) {
                    $isCorrect = true;
                }
                break;
            case QuestionTypeEnum::MULTIPLE_CHOICE->value:
                $actualCorrectChoices = 0;
                $submittedCorrectChoices = 0;

                foreach ($options as $option) {

                    if ($option['is_correct']) {
                        $actualCorrectChoices++;
                    }

                    foreach ($answer['choices'] as $choice) {
                        if ($option['text'] == $choice && $option['is_correct']) {
                            $submittedCorrectChoices++;
                        }
                    }
                }

                // if ($actualCorrectChoices == $submittedCorrectChoices) {
                //     $isCorrect = true;
                // }
                if (count($answer['choices']) === $actualCorrectChoices && $submittedCorrectChoices === $actualCorrectChoices) {
                    $isCorrect = true;
                }

                break;
            case QuestionTypeEnum::SINGLE_CHOICE->value:
                foreach ($options as $option) {
                    if ($option['text'] == $answer['choice'] && $option['is_correct']) {
                        $isCorrect = true;
                    }
                }
                break;
        }

        return $isCorrect;
    }
}
