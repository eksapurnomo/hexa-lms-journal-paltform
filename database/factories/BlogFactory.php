<?php

namespace Database\Factories;

use App\Enum\MediaTypeEnum;
use App\Models\Media;
use App\Repositories\UserRepository;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Blog>
 */
class BlogFactory extends Factory
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
            'user_id' => UserRepository::getAll()->random()->id,
            'media_id' => Media::factory()->create(['type' => MediaTypeEnum::IMAGE])->id,
            'title' => fake()->sentence(),
            'description' => json_encode($description),
            'status' => fake()->boolean(),
        ];
    }
}
