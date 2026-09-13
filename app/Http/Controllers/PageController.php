<?php

namespace App\Http\Controllers;

use App\Http\Resources\PageResource;
use App\Models\Page;
use Illuminate\Http\Request;

class PageController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->input('items_per_page', 10);
        $pageNumber = $request->input('page_number', 1);
        $skip = ($pageNumber - 1) * $perPage;

        $pages = Page::all()->skip($skip)->take($perPage)->values();

        return $this->json($pages ? 'Pages found' : 'No pages found', [
            'total_items' => count($pages),
            'categories' => PageResource::collection($pages),
        ], $pages ? 200 : 404);
    }

    public function show(string $slug)
    {
        return $this->json('Page found', [
            'page' => PageResource::make(Page::where('slug', $slug)->firstOrFail()),
        ]);
    }
}
