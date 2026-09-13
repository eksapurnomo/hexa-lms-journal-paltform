<?php

namespace Database\Seeders;

use App\Models\User;
use App\Repositories\InstructorRepository;
use App\Repositories\UserRepository;
use Illuminate\Support\Str;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Admin User
        $localAdmin = UserRepository::query()->updateOrCreate(
            [
                'email' => 'admin@example.com',
            ],
            [
                'phone' => '011' . rand(100000000, 999999999),
                'name' => 'Administrator',
                'is_active' => true,
                'is_admin' => true,
                'email_verified_at' => now(),
                'password' => Hash::make('secret'),
                'remember_token' => Str::random(10),
            ]
        );

        $localAdmin->assignRole('admin');

        // Admin User
        $admin = UserRepository::query()->updateOrCreate(
            [
                'email' => 'admin@readylms.com',
            ],
            [
                'name' => 'Administrator',
                'is_active' => true,
                'phone' => '0110' . rand(100000000, 999999999),
                'is_admin' => true,
                'is_root' => true,
                'email_verified_at' => now(),
                'password' => Hash::make('secret@123'),
                'remember_token' => Str::random(10),
            ]
        );

        $admin->assignRole('admin');

        // General User
        UserRepository::query()->updateOrCreate(
            [
                'email' => 'user@readylms.com',
            ],
            [
                'name' => 'Demo User',
                'phone' => '0110' . rand(100000000, 999999999),
                'is_active' => true,
                'is_admin' => false,
                'email_verified_at' => now(),
                'password' => Hash::make('secret@123'),
                'remember_token' => Str::random(10)
            ]
        );

        if (app()->isLocal()) {
            User::factory()
                ->count(rand(20, 40))
                ->create();
        }
    }
}
