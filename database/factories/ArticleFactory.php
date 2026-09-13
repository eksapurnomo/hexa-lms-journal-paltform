<?php

namespace Database\Factories;

use App\Enum\MediaTypeEnum;
use App\Models\Media;
use App\Repositories\CategoryRepository;
use App\Repositories\InstructorRepository;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Article>
 */
class ArticleFactory extends Factory
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
            'category_id' => CategoryRepository::getAll()->random()->id,
            'media_id' => Media::factory()->create(['type' => MediaTypeEnum::IMAGE])->id,
            'title' => fake()->sentence(),
            'content' => fake()->text(),
            'is_featured' => fake()->boolean(10),
        ];
    }
}
