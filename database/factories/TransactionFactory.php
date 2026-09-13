<?php

namespace Database\Factories;

use App\Repositories\EnrollmentRepository;
use App\Repositories\UserRepository;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Transaction>
 */
class TransactionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $enrollment = EnrollmentRepository::getAll()->random();

        return [
            'identifier' => substr(str_shuffle(str_repeat('0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', 16)), 0, 16),
            'course_id' => $enrollment->course->id,
            'user_id' => $enrollment->user->id,
            'course_title' => $enrollment->course->title,
            'user_phone' => UserRepository::getAll()->random()->phone,
            'payment_amount' => $enrollment->course->price,
            'payment_method' => 'PayPal',
            'is_paid' => fake()->boolean(80),
            'paid_at' => now()->setHour(fake()->numberBetween(0, -24)),
            'enrollment_id' => $enrollment->id,
            'created_at' => fake()->dateTimeBetween('-1 year', 'now'),
        ];
    }
}
