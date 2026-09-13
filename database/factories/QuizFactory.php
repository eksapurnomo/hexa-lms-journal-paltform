<?php

namespace Database\Factories;

use App\Repositories\CourseRepository;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Exam>
 */
class QuizFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(),
            'duration_per_question' => 30,
            'mark_per_question' => fake()->numberBetween(1, 10),
            'course_id' => CourseRepository::getAll()->random()->id,
        ];
    }
}
