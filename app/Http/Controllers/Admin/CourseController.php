<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\CourseStoreRequest;
use App\Http\Requests\CourseUpdateRequest;
use App\Http\Resources\CourseResource;
use App\Models\Course;
use App\Repositories\CourseRepository;

class CourseController extends Controller
{
    /**
     * Store a newly created resource in storage.
     */
    public function store(CourseStoreRequest $request)
    {
        $course = CourseRepository::storeByRequest($request);

        return $this->json('Course created successfully', [
            'course' => CourseResource::make($course)
        ], 201);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(CourseUpdateRequest $request, Course $course)
    {
        CourseRepository::updateByRequest($request, $course);
        $updatedCourse = CourseRepository::find($course->id);

        return $this->json('Course updated successfully', [
            'course' => CourseResource::make($updatedCourse)
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Course $course)
    {
        $course->delete();

        return $this->json('Course deleted successfully', null, 200);
    }
}
