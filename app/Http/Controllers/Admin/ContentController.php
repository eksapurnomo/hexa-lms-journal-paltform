<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ContentStoreRequest;
use App\Http\Requests\ContentUpdateRequest;
use App\Http\Resources\ContentResource;
use App\Models\Content;
use App\Repositories\ContentRepository;

class ContentController extends Controller
{
    /**
     * Store a newly created resource in storage.
     */
    public function store(ContentStoreRequest $request)
    {
        $content = ContentRepository::storeByRequest($request);

        return $this->json('Content created successfully', [
            'content' => ContentResource::make($content)
        ], 201);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ContentUpdateRequest $request, Content $content)
    {
        ContentRepository::updateByRequest($request, $content);
        $updatedContent = ContentRepository::find($content->id);

        return $this->json('Content updated successfully', [
            'content' => ContentResource::make($updatedContent)
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Content $content)
    {
        $content->delete();

        return $this->json('Content deleted successfully');
    }
}
