<?php

namespace Database\Seeders;

use App\Repositories\SocialMediaRepository;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SocialMediaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $socialMedia = [
            [
                'title' => 'Facebook',
                'icon' => 'bi bi-facebook',
                'url' => null,
            ],
            [
                'title' => 'Twitter',
                'icon' => 'bi bi-twitter',
                'url' => null,
            ],
            [
                'title' => 'Whatsapp',
                'icon' => 'bi bi-whatsapp',
                'url' => null,
            ],
            [
                'title' => 'Linkedin',
                'icon' => 'bi bi-linkedin',
                'url' => null,
            ],
            [
                'title' => 'Instagram',
                'icon' => 'bi bi-instagram',
                'url' => null,
            ],
            [
                'title' => 'Youtube',
                'icon' => 'bi bi-youtube',
                'url' => null,
            ],
            [
                'title' => 'Pinterest',
                'icon' => 'bi bi-pinterest',
                'url' => null,
            ],
            [
                'title' => 'Tiktok',
                'icon' => 'bi bi-tiktok',
                'url' => null,
            ],
            [
                'title' => 'Snapchat',
                'icon' => 'bi bi-snapchat',
                'url' => null,
            ],
            [
                'title' => 'Reddit',
                'icon' => 'bi bi-reddit',
                'url' => null,
            ],
            [
                'title' => 'Vimeo',
                'icon' => 'bi bi-vimeo',
                'url' => null,
            ],
            [
                'title' => 'Twitch',
                'icon' => 'bi bi-twitch',
                'url' => null,
            ],
        ];

        foreach ($socialMedia as $media) {
            $oldMedia = SocialMediaRepository::query()->where('title', $media['title'])->first();
            if ($oldMedia) {
                $oldMedia->delete();
            }
            SocialMediaRepository::query()->updateOrCreate([
                'title' => $media['title'],
            ], $media);
        };
    }
}
