<?php

namespace App\Repositories;

use Abedin\Maker\Repositories\Repository;
use App\Enum\MediaTypeEnum;
use App\Http\Requests\ChapterStoreRequest;
use App\Http\Requests\ChapterUpdateRequest;
use App\Models\Chapter;
use App\Models\Content;
use Owenoj\LaravelGetId3\GetId3;

class ChapterRepository extends Repository
{
    public static function model()
    {
        return Chapter::class;
    }

    public static function storeByRequest(ChapterStoreRequest $request): Chapter
    {
        $chapterId = $request->chapter_id ?? null;
        $chapter = null;

        if (!$chapterId || $chapterId == "null") {
            $chapter = self::create([
                'title' => $request->title,
                'serial_number' => $request->serial_number,
                'course_id' => $request->course_id,
            ]);
        } else {
            $chapter = self::query()->where('id', $chapterId)->first();
        }

        foreach ($request->contents ?? [] as $requestContent) {
            $isFree = false;
            $isForwardAble = false;

            $contentMedia = isset($requestContent['media']) ? MediaRepository::storeByRequest(
                $requestContent['media'],
                'course/chapter/content/media',
                MediaTypeEnum::IMAGE
            ) : null;

            $isForwardAble = isset($requestContent['is_forwardable']) && $requestContent['is_forwardable'] != "0";
            $isFree = isset($requestContent['is_free']) && $requestContent['is_free'] != "0";

            $mediaLink = $requestContent['link'] ?? null;
            $media = $requestContent['media'] ?? null;

            if ($media) {
                $mediaType = self::getFileType($media);
                $mediaDuration = self::getMediaPlaytime($media);
            } elseif ($mediaLink) {
                $mediaType = MediaTypeEnum::VIDEO;
                $mediaDuration = $requestContent['duration'];
            } else {
                throw new \Exception('No media or media link provided.');
            }


            // customize media link
            $customWidth = '100%';
            $customHeight = '450';

            $mediaLink = preg_replace('/\s*title="[^"]*"/', '', $mediaLink);

            // Replace the width and height attributes in the iframe
            $customizedIframe = preg_replace(
                ['/width="\d+"/', '/height="\d+"/'], // Match width and height attributes
                ["width=\"$customWidth\"", "height=\"$customHeight\""], // Replace with custom values
                $mediaLink
            );

            $mediaLink = $customizedIframe;

            ContentRepository::create([
                'chapter_id' => $chapter->id,
                'media_id' => $contentMedia ? $contentMedia->id : null,
                'title' => $requestContent['title'],
                'type' => $mediaType,
                'duration' => $mediaDuration,
                'serial_number' => $requestContent['serial_number'],
                'is_forwardable' => $isForwardAble,
                'is_free' => $isFree,
                'media_link' => $mediaLink,
                'media_updated_at' => now()
            ]);
        }

        return $chapter;
    }

    public static function updateByRequest(ChapterUpdateRequest $request, Chapter $chapter)
    {
        self::update($chapter, [
            'title' => $request->title ?? $chapter->title,
            'serial_number' => $request->serial_number ?? $chapter->serial_number
        ]);

        $newContent = false;

        foreach ($request->deletedIds ?? [] as $id) {
            if ($id) {
                ContentRepository::query()->where('id', $id)?->delete();
            }
        }

        foreach ($request->contents ?? [] as $requestContent) {
            $isFree = false;
            $isForwardAble = false;
            $contentId = $requestContent['content_id'] ?? null;
            $mediaType = MediaTypeEnum::VIDEO; // Ensure it is not an empty string
            $mediaDuration = 0;

            $media = isset($requestContent['media']) ? $requestContent['media'] : null;
            $mediaLink = isset($requestContent['link']) ? $requestContent['link'] : null;

            $contentMedia = self::uploadFile($media);

            $isForwardAble = isset($requestContent['is_forwardable']) && $requestContent['is_forwardable'] != "0";
            $isFree = isset($requestContent['is_free']) && $requestContent['is_free'] != "0";

            if ($media) {
                $mediaType = self::getFileType($media);
                $mediaDuration = self::getMediaPlaytime($media);
            } elseif ($mediaLink) {
                $mediaType = $contentExists?->type ?? MediaTypeEnum::VIDEO; // Keep previous type if exists
                $mediaDuration = $requestContent['duration'] ?? 0;
            }


            // Customize media link
            $customWidth = '100%';
            $customHeight = '450';

            $mediaLink = preg_replace('/\s*title="[^"]*"/', '', $mediaLink);

            $customizedIframe = preg_replace(
                ['/width="\d+"/', '/height="\d+"/'],
                ["width=\"$customWidth\"", "height=\"$customHeight\""],
                $mediaLink
            );

            $mediaLink = $customizedIframe;

            $contentExists = null;

            if ($contentId) {
                $contentExists = ContentRepository::query()->where('id', $contentId)->first();
            }

            if ($contentExists?->id == $contentId || $contentId) {
                ContentRepository::query()->updateOrCreate(
                    ['id' => $contentId],
                    [
                        'chapter_id' => $chapter->id,
                        'media_id' => $mediaLink ? null : ($contentExists?->media_id == null ? $contentMedia->id : $contentExists->media_id),
                        'title' => $requestContent['title'] ?? $contentExists->title,
                        'type' => $contentMedia ? self::getFileType($media) : ($contentExists->type ?? MediaTypeEnum::VIDEO),
                        'duration' => $mediaDuration ?? 0,
                        'serial_number' => $requestContent['serial_number'] ?? $contentExists->serial_number,
                        'is_forwardable' => $isForwardAble ?? $contentExists->is_forwardable,
                        'is_free' => $isFree ?? $contentExists->is_free,
                        'media_link' => $mediaLink ?? ($contentExists?->media_id == null ? $contentExists->media_link : null),
                        'media_updated_at' => now()
                    ]
                );

                $newContent = true;
            }
        }

        return $newContent;
    }



    private static function uploadFile($file)
    {
        return $file ? MediaRepository::storeByRequest(
            $file,
            'course/chapter/content/media',
            self::getFileType($file),
        ) : null;
    }

    private static function getMediaPlaytime($file)
    {
        $mediaType = self::getFileType($file);

        $minutes = 0;

        if ($mediaType == MediaTypeEnum::AUDIO || $mediaType == MediaTypeEnum::VIDEO) {
            $track = GetId3::fromUploadedFile($file);

            $time = explode(':', $track->getPlaytime());
            $minutes = (int) $time[0] ? $time[0] : 1;
        }

        return $minutes;
    }

    private static function getFileType($file)
    {
        switch ($file->getClientMimeType()) {
            case 'image/jpeg':
            case 'image/png':
            case 'image/jpg':
            case 'image/gif':
            case 'image/svg+xml':
                $mediaType = MediaTypeEnum::IMAGE;
                break;
            case 'video/mp4':
            case 'video/mpeg':
                $mediaType = MediaTypeEnum::VIDEO;
                break;
            case 'audio/mpeg':
            case 'audio/wav':
            case 'audio/webm':
            case 'audio/ogg':
            case 'audio/x-wav':
                $mediaType = MediaTypeEnum::AUDIO;
                break;
            case 'application/pdf':
            case 'application/msword':
            case 'application/vnd.openxmlformats-officedocument.wordprocessingml.document':
                $mediaType = MediaTypeEnum::DOCUMENT;
                break;
            default:
                $mediaType = MediaTypeEnum::IMAGE;
                break;
        }

        return $mediaType;
    }
}
