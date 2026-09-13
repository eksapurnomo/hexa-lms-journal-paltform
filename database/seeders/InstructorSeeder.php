<?php

namespace Database\Seeders;

use App\Models\Instructor;
use App\Repositories\InstructorRepository;
use App\Repositories\UserRepository;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class InstructorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // instructor User
        $instructor = UserRepository::create([
            'name' => 'instructor',
            'phone' => '01000000618913',
            'email' => 'instructor@readylms.com',
            'is_active' => false,
            'is_admin' => false,
            'email_verified_at' => now(),
            'password' => Hash::make('secret'),
            'remember_token' => Str::random(10),
        ]);

        InstructorRepository::create([
            'user_id' => $instructor->id,
            'title' => 'instructor',
            'is_featured' => true,
            'created_at' => now(),
        ]);

        $instructor->assignRole('instructor');

        Instructor::factory()
            ->count(10)
            ->create();
    }
}
