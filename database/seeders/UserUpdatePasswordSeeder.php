<?php

namespace Database\Seeders;

use App\Models\User;
use App\Repositories\UserRepository;
use Illuminate\Support\Str;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserUpdatePasswordSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = UserRepository::query()->where('email', 'admin@readylms.com')->first();

        $user->update([
            'password' => Hash::make('secret@123'),
        ]);
    }
}
