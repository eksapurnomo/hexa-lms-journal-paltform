<?php

namespace Database\Factories;

use App\Repositories\CourseRepository;
use App\Repositories\UserRepository;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Review>
 */
class ReviewFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => UserRepository::getAll()->random()->id,
            'course_id' => CourseRepository::getAll()->random()->id,
            'rating' => fake()->numberBetween(1, 5),
            'comment' => fake()->text(100),
        ];
    }
}
