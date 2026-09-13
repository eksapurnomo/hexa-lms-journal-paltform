<?php

namespace Database\Factories;

use App\Models\Media;
use App\Repositories\ChapterRepository;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Content>
 */
class ContentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $media = Media::factory()->create();

        return [
            'chapter_id' => ChapterRepository::getAll()->random()->id,
            'title' => fake()->sentence(),
            'media_id' => $media->id,
            'type' => $media->type,
            'duration' => fake()->numberBetween(60, 3600),
            'serial_number' => fake()->randomNumber(),
        ];
    }
}
