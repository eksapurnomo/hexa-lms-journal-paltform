<?php

namespace App\Repositories;

use Abedin\Maker\Repositories\Repository;
use App\Enum\MediaTypeEnum;
use App\Http\Requests\CategoryStoreRequest;
use App\Http\Requests\CategoryUpdateRequest;
use App\Models\Category;

class CategoryRepository extends Repository
{
    public static function model()
    {
        return Category::class;
    }

    public static function storeByRequest(CategoryStoreRequest $request)
    {
        $isFeatured = false;

        if (isset($request->is_featured)) {
            $isFeatured = $request->is_featured == 'on' ? true : false;
        }

        $media = $request->hasFile('media') ? MediaRepository::storeByRequest(
            $request->file('media'),
            'category/image',
            MediaTypeEnum::IMAGE
        ) : null;

        return self::create([
            'title' => $request->title,
            'media_id' => $media ? $media->id : null,
            'is_featured' => $isFeatured,
            'color' => $request->color
        ]);
    }

    public static function updateByRequest(CategoryUpdateRequest $request, Category $category)
    {
        $isFeatured = false;

        if (isset($request->is_featured)) {
            $isFeatured = $request->is_featured == 'on' ? true : false;
        }

        if ($category->image) {
            $media = $request->hasFile('media') ? MediaRepository::updateByRequest(
                $request->file('media'),
                $category->image,
                'category/image',
                MediaTypeEnum::IMAGE
            ) : $category->image;
        } else {
            $media = $request->hasFile('media') ? MediaRepository::storeByRequest(
                $request->file('media'),
                'category/image',
                MediaTypeEnum::IMAGE
            ) : null;
        }

        return self::update($category, [
            'title' => $request->title ?? $category->title,
            'media_id' => $media ? $media->id : null,
            'is_featured' => $isFeatured,
            'color' => $request->color ?? $category->color
        ]);
    }
}
