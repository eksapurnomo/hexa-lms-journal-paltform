<?php

namespace Database\Factories;

use App\Enum\MediaTypeEnum;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Media>
 */
class MediaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $types = MediaTypeEnum::cases();

        $randomType = $types[array_rand($types)];

        switch ($randomType) {
            case MediaTypeEnum::IMAGE:
                return [
                    'type' => $randomType->value,
                    'src' => 'media/dummy-image.jpg',
                    'path' => 'media/',
                    'extension' => 'jpg'
                ];
                break;
            case MediaTypeEnum::AUDIO:
                return [
                    'type' => $randomType->value,
                    'src' => 'media/dummy-audio.mp3',
                    'path' => 'media/',
                    'extension' => 'mp3'
                ];
                break;
            case MediaTypeEnum::VIDEO:
                return [
                    'type' => $randomType->value,
                    'src' => 'media/dummy-video.mp4',
                    'path' => 'media/',
                    'extension' => 'mp4'
                ];
                break;
            case MediaTypeEnum::DOCUMENT:
                return [
                    'type' => $randomType->value,
                    'src' => 'media/dummy-document.pdf',
                    'path' => 'media/',
                    'extension' => 'pdf'
                ];
                break;
        }
    }
}
