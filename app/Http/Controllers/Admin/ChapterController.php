<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ChapterStoreRequest;
use App\Http\Requests\ChapterUpdateRequest;
use App\Http\Resources\ChapterResource;
use App\Models\Chapter;
use App\Repositories\ChapterRepository;

class ChapterController extends Controller
{
    /**
     * Store a newly created resource in storage.
     */
    public function store(ChapterStoreRequest $request)
    {
        $chapter = ChapterRepository::storeByRequest($request);

        return $this->json('Chapter created successfully', [
            'chapter' => ChapterResource::make($chapter)
        ], 201);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ChapterUpdateRequest $request, Chapter $chapter)
    {
        ChapterRepository::updateByRequest($request, $chapter);
        $updatedChapter = ChapterRepository::find($chapter->id);

        return $this->json('Chapter updated successfully', [
            'chapter' => ChapterResource::make($updatedChapter)
        ], 201);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Chapter $chapter)
    {
        $chapter->delete();

        return $this->json('Chapter deleted successfully');
    }
}
