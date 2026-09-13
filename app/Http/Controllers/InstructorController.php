<?php

namespace App\Http\Controllers;

use App\Http\Resources\CourseResource;
use App\Http\Resources\InstructorResource;
use App\Models\Instructor;
use App\Repositories\InstructorRepository;
use Illuminate\Http\Request;

class InstructorController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->input('items_per_page');
        $pageNumber = $request->input('page_number');
        $skip = ($pageNumber - 1) * $perPage;

        $isFeatured = $request->is_featured;

        $instructors = InstructorRepository::query()->when($isFeatured, function ($query) {
            $query->where('is_featured', true);
        });

        $total = $instructors->count();

        $instructors = $instructors->when($perPage && $skip, function ($query) use ($perPage, $skip) {
            $query->skip($skip)->take($perPage);
        })->get();


        return $this->json('Instructors found', [
            'instructors' => InstructorResource::collection($instructors),
            'total_items' => $total,
        ]);
    }

    public function show(Instructor $instructor)
    {
        return $this->json('Instructor found', [
            'instructor' => InstructorResource::make($instructor),
            'courses' => CourseResource::collection($instructor->courses),
        ]);
    }
}
