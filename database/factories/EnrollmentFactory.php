<?php

namespace Database\Factories;

use App\Repositories\CouponRepository;
use App\Repositories\CourseRepository;
use App\Repositories\UserRepository;
use Illuminate\Database\Eloquent\Factories\Factory;

class EnrollmentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $course = CourseRepository::getAll()->random();

        return [
            'user_id' => UserRepository::getAll()->random()->id,
            'course_id' => $course->id,
            'coupon_id' => CouponRepository::getAll()->random()->id,
            'course_progress' => 0.00,
            'course_price' => $course->price,
            'discount_amount' => fake()->numberBetween(0, $course->price),
            'last_activity' => now()->setHour(fake()->numberBetween(0, -24)),
            'is_certificate_downloaded' => fake()->boolean(),
        ];
    }
}
