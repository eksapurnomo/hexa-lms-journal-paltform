<?php

namespace App\Repositories;

use Abedin\Maker\Repositories\Repository;
use App\Http\Requests\ReviewStoreRequest;
use App\Models\Course;
use App\Models\Review;

class ReviewRepository extends Repository
{
    public static function model()
    {
        return Review::class;
    }

    public static function storeByRequest(ReviewStoreRequest $request, Course $course)
    {
        return self::create([
            'user_id' => auth()->id(),
            'course_id' => $course->id,
            'comment' => $request->comment,
            'rating' => $request->rating
        ]);
    }
}
