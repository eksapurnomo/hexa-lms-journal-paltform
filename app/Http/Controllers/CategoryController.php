<?php

namespace App\Http\Controllers;

use App\Http\Resources\CategoryResource;
use App\Repositories\CategoryRepository;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->input('items_per_page', 100);
        $pageNumber = $request->input('page_number', 1);
        $skip = ($pageNumber - 1) * $perPage;

        $query = CategoryRepository::query();

        // Search
        if ($request->has('search')) {
            $searchTerm = $request->input('search');
            $query->where('title', 'like', '%' . $searchTerm . '%');
        }

        if ($request->has('is_featured')) {
            $isFeatured = $request->input('is_featured');

            if ($isFeatured == 'true') {
                $query->where('is_featured', true);
            } elseif ($isFeatured == 'false') {
                $query->where('is_featured', false);
            }
        }
        $categories = $query->skip($skip)->take($perPage)->orderBy('display_order')->get();

        return $this->json($categories ? 'Categories found' : 'No categories found', [
            'total_items' => count($categories),
            'categories' => CategoryResource::collection($categories),
        ], $categories ? 200 : 404);
    }
}
