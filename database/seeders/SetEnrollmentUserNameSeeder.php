<?php

namespace Database\Seeders;

use App\Repositories\EnrollmentRepository;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SetEnrollmentUserNameSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $nullEnrollmentUsers = EnrollmentRepository::query()->where('certificate_user_name', null)->get();

        foreach ($nullEnrollmentUsers as $enrollment) {

            EnrollmentRepository::query()->where('id', $enrollment->id)->update([
                'certificate_user_name' => $enrollment->user->name
            ]);
        }
    }
}
