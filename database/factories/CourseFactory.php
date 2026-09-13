<?php

namespace Database\Factories;

use App\Enum\MediaTypeEnum;
use App\Models\Media;
use App\Repositories\CategoryRepository;
use App\Repositories\InstructorRepository;
use App\Repositories\MediaRepository;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Course>
 */
class CourseFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $description = [];

        for ($i = 0; $i < 2; $i++) {
            $randomText = fake()->text(1000);
            $sentence1 = fake()->sentence();
            $sentence2 = fake()->sentence();
            $sentence3 = fake()->sentence();
            $sentence4 = fake()->sentence();
            $sentence5 = fake()->sentence();

            $description[] = [
                'heading' => fake()->sentence(5),
                'body' => "
                    <p>$randomText</p>
                ",
            ];

            $description[] = [
                'heading' => fake()->sentence(5),
                'body' => "
                    <ul>
                        <li>$sentence1</li>
                        <li>$sentence2</li>
                        <li>$sentence3</li>
                        <li>$sentence4</li>
                        <li>$sentence5</li>
                    </ul>
                ",
            ];
        }

        return [
            'category_id' => CategoryRepository::getAll()->random()->id,
            'title' => fake()->sentence(),
            'media_id' => Media::factory()->create(['type' => MediaTypeEnum::IMAGE])->id,
            'video_id' => Media::factory()->create(['type' => MediaTypeEnum::VIDEO])->id,
            'description' => json_encode($description),
            'view_count' => fake()->numberBetween(1000, 10000),
            'regular_price' => fake()->randomFloat(1, 10000, 90000),
            'price' => fake()->randomFloat(1, 1000, 9000),
            'instructor_id' => InstructorRepository::getAll()->random()->id,
            'published_at' => now()->setHour(fake()->numberBetween(0, -24)),
            'is_active' => fake()->boolean(80),
        ];
    }
}
