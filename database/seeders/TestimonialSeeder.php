<?php

namespace Database\Seeders;

use App\Enum\MediaTypeEnum;
use App\Models\Media;
use App\Models\Testimonial;
use Database\Factories\TestimonialFactory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TestimonialSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        TestimonialFactory::new()->count(10)->create();
    }
}
