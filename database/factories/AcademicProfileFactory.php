<?php

namespace Database\Factories;

use App\Models\AcademicProfile;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class AcademicProfileFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = AcademicProfile::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'user_id' => User::factory(),
            'highest_degree' => $this->faker->randomElement(['PhD', 'Master', 'Bachelor']),
            'academic_position' => $this->faker->randomElement(['Professor', 'Associate Professor', 'Assistant Professor', 'Researcher']),
            'institution' => $this->faker->company . ' University',
            'department' => 'Department of ' . $this->faker->jobTitle,
            'country' => $this->faker->country,
            'biography' => $this->faker->paragraph,
            'research_interests' => $this->faker->words(3, true),
            'expertise' => $this->faker->words(4),
            'orcid' => '0000-000' . $this->faker->numberBetween(1, 9) . '-' . $this->faker->randomNumber(4, true) . '-' . $this->faker->randomNumber(4, true),
            'scopus_author_id' => $this->faker->randomNumber(9, true),
            'google_scholar_url' => 'https://scholar.google.com/citations?user=' . $this->faker->lexify('????????????'),
        ];
    }
}
