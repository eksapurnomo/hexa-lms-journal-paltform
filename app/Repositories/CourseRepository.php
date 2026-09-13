<?php

namespace App\Repositories;

use Abedin\Maker\Repositories\Repository;
use App\Enum\MediaTypeEnum;
use App\Http\Requests\CourseStoreRequest;
use App\Http\Requests\CourseUpdateRequest;
use App\Models\Course;

class CourseRepository extends Repository
{
    public static function model()
    {
        return Course::class;
    }

    public static function storeByRequest(CourseStoreRequest $request)
    {
        $isActive = false;

        if (isset($request->is_active)) {
            $isActive = $request->is_active === "on" ?? true;
        }

        $media = $request->hasFile('media') ? MediaRepository::storeByRequest(
            $request->file('media'),
            'course/thumbnail',
            MediaTypeEnum::IMAGE
        ) : null;

        $video = $request->hasFile('video') ? MediaRepository::storeByRequest(
            $request->file('video'),
            'course/video',
            MediaTypeEnum::VIDEO
        ) : null;



        $course = self::create([
            'category_id' => $request->category_id,
            'title' => $request->title,
            'media_id' => $media ? $media->id : null,
            'video_id' => $video ? $video->id : null,
            'description' => json_encode($request->description),
            'regular_price' => $request->regular_price,
            'price' => $request->price,
            'instructor_id' => $request->instructor_id,
            'is_active' => $isActive,
            'published_at' => $request->is_active ? now() : null
        ]);

        foreach ($request->chapters ?? [] as $requestChapter) {
            $chapter = ChapterRepository::create([
                'title' => $requestChapter['title'],
                'serial_number' => $requestChapter['serial_number'],
                'course_id' => $course->id
            ]);

            foreach ($requestChapter['contents'] as $requestContent) {
                $contentMedia = isset($requestContent['media']) ? MediaRepository::storeByRequest(
                    $requestContent['media'],
                    'course/chapter/content/media',
                    MediaTypeEnum::IMAGE
                ) : null;

                ContentRepository::create([
                    'chapter_id' => $chapter->id,
                    'media_id' => $contentMedia ? $contentMedia->id : null,
                    'title' => $requestContent['title'],
                    'type' => $requestContent['type'],
                    'serial_number' => $requestContent['serial_number']
                ]);
            }
        }

        return $course;
    }

    public static function updateByRequest(CourseUpdateRequest $request, Course $course)
    {

        $isActive = false;

        if (isset($request->is_active)) {
            $isActive = $request->is_active === "on" ?? true;
        }

        $media = $course->media;
        if ($request->hasFile('media')) {
            $media = MediaRepository::updateByRequest(
                $request->file('media'),
                $media,
                'course/thumbnail',
                MediaTypeEnum::IMAGE
            );
        }

        $video = $course->video;
        if ($request->hasFile('video')) {
            $video = MediaRepository::updateOrCreateByRequest(
                $request->file('video'),
                'course/video',
                $video,
                MediaTypeEnum::VIDEO
            );
        }

        if ($course->video) {
            $video = $request->hasFile('video') ? MediaRepository::updateByRequest(
                $request->file('video'),
                $course->video,
                'course/video',
                MediaTypeEnum::VIDEO
            ) : $course->video;
        } else {
            $video = $request->hasFile('video') ? MediaRepository::storeByRequest(
                $request->file('video'),
                'course/video',
                MediaTypeEnum::VIDEO
            ) : null;
        }

        return self::update($course, [
            'category_id' => $request->category_id ?? $course->category_id,
            'title' => $request->title ?? $course->title,
            'media_id' => $media ? $media->id : $course->media->id,
            'video_id' => $video ? $video->id : null,
            'description' => json_encode($request->description) ?? $course->description,
            'regular_price' => $request->regular_price ?? null,
            'price' => $request->price,
            'instructor_id' => $request->instructor_id ?? $course->instructor_id,
            'is_active' => $isActive,
            'published_at' => $request->is_active == 'on' ? now() : null
        ]);
    }
}
