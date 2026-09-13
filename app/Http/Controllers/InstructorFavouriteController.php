<?php

namespace App\Http\Controllers;

use App\Http\Resources\InstructorResource;
use App\Models\Instructor;
use App\Repositories\GuestRepository;
use App\Repositories\InstructorRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InstructorFavouriteController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->input('items_per_page', 10);
        $pageNumber = $request->input('page_number', 1);
        $skip = ($pageNumber - 1) * $perPage;

        /** @var User */
        $loggedInUser = Auth::guard('api')->user();

        if ($loggedInUser) {
            $favourteInstructors = InstructorRepository::query()
                ->whereHas('favouriteUsers', function ($query) use ($loggedInUser) {
                    $query->where('id', $loggedInUser->id);
                })
                ->get()->skip($skip)->take($perPage)->values();
        } else {
            $guest = GuestRepository::query()->where('unique_id', '=', $request->input('guest_id'))->first();

            if (!$guest) {
                return $this->json('Unauthenticated and non existing guest', null, 401);
            }

            $favourteInstructors = InstructorRepository::query()
                ->whereHas('favouriteGuests', function ($query) use ($guest) {
                    $query->where('guest_id', $guest->id);
                })
                ->get()->skip($skip)->take($perPage)->values();
        }

        return $this->json($favourteInstructors->count() > 0 ? 'Instructors found' : 'No instructors found', [
            'total_items' => count($favourteInstructors),
            'instructors' => InstructorResource::collection($favourteInstructors),
        ], $favourteInstructors ? 200 : 404);
    }

    public function modify(Request $request, Instructor $instructor)
    {
        /** @var User */
        $loggedInUser = Auth::guard('api')->user();

        if (!$loggedInUser) {
            $guest = GuestRepository::query()->where('unique_id', '=', $request->input('guest_id'))->first();

            if (!$guest) {
                return $this->json('Unauthenticated and non existing guest', null, 401);
            }

            if ($guest->favouriteInstructors()->where('id', $instructor->id)->exists()) {
                $guest->favouriteInstructors()->detach($instructor);

                return $this->json('Removed from favourites', ['is_added' => false], 200);
            } else {
                $guest->favouriteInstructors()->attach($instructor);

                return $this->json('Added to favourites', ['is_added' => true], 200);
            }
        }

        if ($loggedInUser->favouriteInstructors()->where('id', $instructor->id)->exists()) {
            $loggedInUser->favouriteInstructors()->detach($instructor);

            return $this->json('Removed from favourites', ['is_added' => false], 200);
        } else {
            $loggedInUser->favouriteInstructors()->attach($instructor);

            return $this->json('Added to favourites', ['is_added' => true], 200);
        }
    }
}
