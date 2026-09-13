<?php

namespace Database\Factories;

use App\Repositories\UserRepository;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Instructor>
 */
class InstructorFactory extends Factory
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
            'title' => fake()->jobTitle(),
            'is_featured' => fake()->boolean(20),
            'about' => fake()->text(100),
        ];
    }
}
