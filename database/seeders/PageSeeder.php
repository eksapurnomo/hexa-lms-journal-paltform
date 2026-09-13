<?php

namespace Database\Seeders;

use App\Models\Page;
use App\Repositories\PageRepository;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Page::truncate();

        $slugs = ['privacy_policy', 'terms_and_conditions', 'refund_policy', 'about_us', 'contact_us', 'faq'];

        foreach ($slugs as $slug) {
            PageRepository::create([
                'title' => ucfirst(str_replace('_', ' ', $slug)),
                'slug' => $slug,
                'content' => fake()->randomHtml()
            ]);
        }
    }
}
