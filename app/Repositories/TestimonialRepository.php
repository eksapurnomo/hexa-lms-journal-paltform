<?php

namespace App\Repositories;

use Abedin\Maker\Repositories\Repository;
use App\Enum\MediaTypeEnum;
use App\Models\Testimonial;

class TestimonialRepository extends Repository
{
    public static function model()
    {
        return Testimonial::class;
    }

    public static function storeByRequest($request)
    {
        $isActive = $request->has('is_active') ? true : false;

        $picture = $request->hasFile('picture') ? MediaRepository::storeByRequest(
            $request->file('picture'),
            'testimonial/picture',
            MediaTypeEnum::IMAGE,
        ) : null;

        return self::create([
            'name' => strtolower($request->name),
            'designation' => strtolower($request->designation),
            'description' => $request->description,
            'media_id' => $picture->id ?? null,
            'rating' => $request->rating,
            'is_active' => $isActive,
        ]);
    }

    public static function updateByRequest($request, Testimonial $testimonial)
    {
        $isActive = $request->has('is_active') ? true : false;

        $picture = $request->hasFile('picture') ? MediaRepository::updateByRequest(
            $request->file('picture'),
            $testimonial->media,
            'testimonial/picture',
            MediaTypeEnum::IMAGE,
        ) : null;

        return self::update($testimonial, [
            'name' => strtolower($request->name) ?? $testimonial->name,
            'designation' => strtolower($request->designation) ?? $testimonial->designation,
            'description' => $request->description ?? $testimonial->description,
            'media_id' => $picture->id ?? $testimonial->media_id,
            'rating' => $request->rating ?? $testimonial->rating,
            'is_active' => $isActive ?? $testimonial->is_active,
        ]);
    }
}
