<?php

namespace Database\Factories;

use App\Repositories\InstructorRepository;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Experience>
 */
class ExperienceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'instructor_id' => InstructorRepository::getAll()->random()->id,
            'role' => fake()->jobTitle(),
            'company_name' => fake()->sentence(3),
            'from_year' => fake()->year(),
            'to_year' => fake()->year(),
            'description' => fake()->text(100),
        ];
    }
}
