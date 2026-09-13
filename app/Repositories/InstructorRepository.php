<?php

namespace App\Repositories;

use Abedin\Maker\Repositories\Repository;
use App\Enum\MediaTypeEnum;
use App\Http\Requests\InstructorStoreRequest;
use App\Http\Requests\InstructorUpdateRequest;
use App\Models\Guest;
use App\Models\Instructor;
use App\Models\User;

class InstructorRepository extends Repository
{
    public static function model()
    {
        return Instructor::class;
    }

    public static function storeByRequest(InstructorStoreRequest $request, $userId)
    {
        $isFeatured = false;

        if (isset($request->is_featured)) {
            $isFeatured = $request->is_featured == 'on' ? true : false;
        }

        return self::create([
            'user_id' => $userId,
            'title' => $request->title,
            'is_featured' => $isFeatured,
            'about' => $request->about
        ]);
    }

    public static function updateByRequest(InstructorUpdateRequest $request, Instructor $instructor)
    {
        if ($instructor->signature) {
            $signature = $request->hasFile('signature') ? MediaRepository::updateByRequest(
                $request->file('signature'),
                $instructor->signature,
                'instructor/signature',
                MediaTypeEnum::IMAGE
            ) : $instructor->signature;
        } else {
            $signature = $request->hasFile('signature') ? MediaRepository::storeByRequest(
                $request->file('signature'),
                'instructor/signature',
                MediaTypeEnum::IMAGE
            ) : null;
        }

        $isFeatured = false;

        if (isset($request->is_featured)) {
            $isFeatured = $request->is_featured == 'on' ? true : false;
        }

        UserRepository::find($instructor->user_id)->update([
            'signature_id' => $signature ? $signature->id : null,
        ]);

        return self::update($instructor, [
            'user_id' => $request->user_id ?? $instructor->user_id,
            'title' => $request->title ?? $instructor->title,
            'is_featured' => $isFeatured,
            'about' => $request->about ?? $instructor->about,
            'signature_id' => $signature ? $signature->id : null,
        ]);
    }
}
