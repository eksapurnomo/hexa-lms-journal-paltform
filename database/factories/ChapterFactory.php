<?php

namespace Database\Factories;

use App\Repositories\CourseRepository;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Chapter>
 */
class ChapterFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'course_id' => CourseRepository::getAll()->random()->id,
            'title' => fake()->sentence(),
            'serial_number' => fake()->randomNumber(),
        ];
    }
}
