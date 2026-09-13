<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\CategoryStoreRequest;
use App\Http\Requests\CategoryUpdateRequest;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use App\Repositories\CategoryRepository;

class CategoryController extends Controller
{
    /**
     * Store a newly created resource in storage.
     */
    public function store(CategoryStoreRequest $request)
    {
        $category = CategoryRepository::storeByRequest($request);

        return $this->json('Category created successfully', [
            'category' => CategoryResource::make($category)
        ], 201);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(CategoryUpdateRequest $request, Category $category)
    {
        CategoryRepository::updateByRequest($request, $category);
        $updatedCategory = CategoryRepository::find($category->id);

        return $this->json('Category updated successfully', [
            'category' => CategoryResource::make($updatedCategory)
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Category $category)
    {
        $category->delete();

        return $this->json('Category deleted successfully', null, 200);
    }
}
