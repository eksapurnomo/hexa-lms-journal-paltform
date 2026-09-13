<?php

namespace Database\Factories;

use App\Repositories\CourseRepository;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Exam>
 */
class ExamFactory extends Factory
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
            'duration' => fake()->numberBetween(20, 60),
            'mark_per_question' => fake()->numberBetween(1, 10),
            'pass_marks' => fake()->numberBetween(10, 15),
            'course_id' => CourseRepository::getAll()->random()->id,
        ];
    }
}
