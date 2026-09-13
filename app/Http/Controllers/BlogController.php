<?php

namespace App\Http\Controllers;

use App\Http\Resources\BlogResource;
use App\Models\Blog;
use App\Repositories\BlogRepository;
use Illuminate\Http\Request;

class BlogController extends Controller
{
    public function index(Request $request)
    {
        // Search
        $query = BlogRepository::query()->where('status', true);

        if ($request->has('search')) {
            $searchTerm = $request->input('search');
            $query->where('title', 'like', '%' . $searchTerm . '%');
        }
        // Pagination
        $totalItems = $query->count();

        $perPage = $request->input('items_per_page', 15);
        $pageNumber = $request->input('page_number', 1);
        $skip = ($pageNumber - 1) * $perPage;
        $blogs = $query->skip($skip)->take($perPage)->get();

        return $this->json($blogs->count() > 0 ? 'blogs found' : 'No blogs found', [
            'total_blogs' => $totalItems,
            'total_items' => count($blogs),
            'blogs' => BlogResource::collection($blogs),
        ], $blogs ? 200 : 404);
    }

    public function show(Blog $blog)
    {
        $url = url('/blog/details/' . $blog->id);
        return $this->json($blog->count() > 0 ? 'blog found' : 'No blog found', [
            'blog' => BlogResource::make($blog),
            'shareable_url' => $url,
        ], $blog ? 200 : 404);
    }
}
