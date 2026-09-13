<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Coupon>
 */
class CouponFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => \strtoupper(fake()->word()),
            'discount' => fake()->numberBetween(5, 75),
            'applicable_from' => now()->subDays(30),
            'valid_until' => now()->addDays(30),
            'is_active' => fake()->boolean(80),
        ];
    }
}
