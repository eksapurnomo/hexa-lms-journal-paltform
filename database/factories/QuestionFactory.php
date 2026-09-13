<?php

namespace Database\Factories;

use App\Enum\QuestionTypeEnum;
use App\Repositories\ExamRepository;
use App\Repositories\QuizRepository;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Question>
 */
class QuestionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $questionType = QuestionTypeEnum::cases()[array_rand(QuestionTypeEnum::cases())];

        switch ($questionType) {
            case QuestionTypeEnum::MULTIPLE_CHOICE:
                $options = [
                    'option_1' => [
                        'text' => fake()->sentence(),
                        'is_correct' => fake()->boolean(),
                    ],
                    'option_2' => [
                        'text' => fake()->sentence(),
                        'is_correct' => fake()->boolean(),
                    ],
                    'option_3' => [
                        'text' => fake()->sentence(),
                        'is_correct' => fake()->boolean(),
                    ],
                    'option_4' => [
                        'text' => fake()->sentence(),
                        'is_correct' => fake()->boolean(),
                    ],
                ];
                break;
            case QuestionTypeEnum::SINGLE_CHOICE:
                $options = [
                    'option_1' => [
                        'text' => fake()->sentence(),
                        'is_correct' => false,
                    ],
                    'option_2' => [
                        'text' => fake()->sentence(),
                        'is_correct' => false,
                    ],
                    'option_3' => [
                        'text' => fake()->sentence(),
                        'is_correct' => false,
                    ],
                    'option_4' => [
                        'text' => fake()->sentence(),
                        'is_correct' => true,
                    ],
                ];
                break;
            case QuestionTypeEnum::BINARY:
                $options = [
                    'yes' => [
                        'is_correct' => false,
                    ],
                    'no' => [
                        'is_correct' => true,
                    ],
                ];
                break;
        }

        $owners = ['exam', 'quiz'];
        $belongsTo = $owners[array_rand($owners)];

        return [
            'course_id' => 1,
            'exam_id' => $belongsTo === 'exam' ? ExamRepository::getAll()->random()->id : null,
            'quiz_id' => $belongsTo === 'quiz' ? QuizRepository::getAll()->random()->id : null,
            'question_text' => fake()->sentence(),
            'question_type' => $questionType->value,
            'options' => json_encode($options),
        ];
    }
}
