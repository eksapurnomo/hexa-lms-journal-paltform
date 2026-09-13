<?php

namespace Database\Factories;

use App\Enum\MediaTypeEnum;
use App\Models\Media;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Category>
 */
class CategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => \ucwords(fake()->word()),
            'media_id' => Media::factory()->create(['type' => MediaTypeEnum::IMAGE])->id,
            'is_featured' => fake()->boolean(50),
            'color' => fake()->hexColor(),
        ];
    }
}
