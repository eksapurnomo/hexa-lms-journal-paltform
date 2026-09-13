<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\InstructorStoreRequest;
use App\Http\Requests\InstructorUpdateRequest;
use App\Http\Resources\InstructorResource;
use App\Models\Instructor;
use App\Repositories\InstructorRepository;
use Illuminate\Http\Request;

class InstructorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $perPage = $request?->input('items_per_page');
        $pageNumber = $request?->input('page_number');
        $skip = ($pageNumber - 1) * $perPage;

        $instructors = InstructorRepository::query();
        $total = $instructors->count();

        $instructors = $instructors->when($perPage && $skip, function ($query) use ($perPage, $skip) {
            $query->skip($skip)->take($perPage);
        })->get();

        return $this->json($instructors ? 'Instructors found' : 'No instructor found', [
            'total_items' => $total,
            'instructors' => InstructorResource::collection($instructors),
        ], $instructors ? 200 : 404);
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(InstructorStoreRequest $request)
    {
        $instructor = InstructorRepository::storeByRequest($request);

        return $this->json('Instructor created successfully', [
            'instructor' => InstructorResource::make($instructor)
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Instructor $instructor)
    {
        return $this->json('Instructor found', [
            'instructor' => InstructorResource::make($instructor)
        ]);
    }

    /**
     * Display the self specified resource.
     */
    public function me()
    {
        if (!auth()->user()->instructor) {
            return $this->json('Instructor profile does not exist');
        }

        return $this->json('Instructor found', [
            'instructor' => InstructorResource::make(auth()->user()->instructor)
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(InstructorUpdateRequest $request, Instructor $instructor)
    {
        InstructorRepository::updateByRequest($request, $instructor);
        $updatedInstructor = InstructorRepository::find($instructor->id);

        return $this->json('Instructor updated successfully', [
            'instructor' => InstructorResource::make($updatedInstructor)
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Instructor $instructor)
    {
        $instructor->delete();

        return $this->json('Instructor profile deleted successfully');
    }
}
