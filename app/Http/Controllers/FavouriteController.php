<?php

namespace App\Http\Controllers;

use App\Http\Resources\CourseResource;
use App\Models\Course;
use App\Repositories\CourseRepository;
use App\Repositories\GuestRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FavouriteController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->input('items_per_page', 10);
        $pageNumber = $request->input('page_number', 1);
        $skip = ($pageNumber - 1) * $perPage;

        /** @var User */
        $loggedInUser = Auth::guard('api')->user();

        if ($loggedInUser) {
            $favourteCourses = CourseRepository::query()
                ->whereHas('favouriteUsers', function ($query) use ($loggedInUser) {
                    $query->where('id', $loggedInUser->id);
                })
                ->get()->skip($skip)->take($perPage)->values();
        } else {
            $guest = GuestRepository::query()->where('unique_id', '=', $request->input('guest_id'))->first();

            if (!$guest) {
                return $this->json('Unauthenticated and non existing guest', null, 401);
            }

            $favourteCourses = CourseRepository::query()
                ->whereHas('favouriteGuests', function ($query) use ($guest) {
                    $query->where('guest_id', $guest->id);
                })
                ->get()->skip($skip)->take($perPage)->values();
        }

        return $this->json($favourteCourses ? 'Courses found' : 'No courses found', [
            'total_items' => count($favourteCourses),
            'courses' => CourseResource::collection($favourteCourses),
        ], $favourteCourses ? 200 : 404);
    }

    public function modify(Request $request, Course $course)
    {
        /** @var User */
        $loggedInUser = Auth::guard('api')->user();

        if (!$loggedInUser) {
            $guest = GuestRepository::query()->where('unique_id', '=', $request->input('guest_id'))->first();

            if (!$guest) {
                return $this->json('Unauthenticated and non existing guest', null, 401);
            }

            if ($guest->favouriteCourses()->where('id', $course->id)->exists()) {
                $guest->favouriteCourses()->detach($course);

                return $this->json('Removed from favourites', ['is_added' => false], 200);
            } else {
                $guest->favouriteCourses()->attach($course);

                return $this->json('Added to favourites', ['is_added' => true], 200);
            }
        }

        if ($loggedInUser->favouriteCourses()->where('id', $course->id)->exists()) {
            $loggedInUser->favouriteCourses()->detach($course);

            return $this->json('Removed from favourites', ['is_added' => false], 200);
        } else {
            $loggedInUser->favouriteCourses()->attach($course);

            return $this->json('Added to favourites', ['is_added' => true], 200);
        }
    }
}
