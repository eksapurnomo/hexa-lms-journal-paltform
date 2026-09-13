<?php

namespace Database\Factories;

use App\Enum\MediaTypeEnum;
use App\Models\Media;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Testimonial>
 */
class TestimonialFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->name,
            'media_id' => Media::factory()->create(['type' => MediaTypeEnum::IMAGE])->id,
            'designation' => $this->faker->jobTitle,
            'description' => fake()->text(100),
            'rating' => $this->faker->numberBetween(1, 5),
            'is_active' => fake()->boolean(),
        ];
    }
}
