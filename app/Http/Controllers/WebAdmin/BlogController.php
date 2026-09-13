<?php

namespace App\Http\Controllers\WebAdmin;

use App\Enum\MediaTypeEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\BlogStoreRequest;
use App\Http\Requests\BlogUpdateRequest;
use App\Http\Requests\UserStoreRequest;
use App\Models\Blog;
use App\Repositories\BlogRepository;
use App\Repositories\MediaRepository;
use Illuminate\Http\Request;

class BlogController extends Controller
{
    public function index()
    {
        return view('blog.index', [
            'blogs' => BlogRepository::query()->withTrashed()->latest('id')->get(),
        ]);
    }
    public function create()
    {
        return view('blog.create');
    }
    public function store(BlogStoreRequest $request)
    {
        $blog = BlogRepository::storeByRequest($request);

        return to_route('blog.index')->withSuccess('Blog created');
    }
    public function edit(Blog $blog)
    {
        return view('blog.edit', compact('blog'));
    }

    public function update(BlogUpdateRequest $request, Blog $blog)
    {
        BlogRepository::updateByRequest($request, $blog);
        return to_route('blog.index')->withSuccess('Blog updated');
    }

    public function delete(Blog $blog)
    {
        $blog->delete();
        return redirect()->route('blog.index')->withSuccess('Blog deleted');
    }
    public function restore(int $id)
    {
        BlogRepository::query()->onlyTrashed()->find($id)->restore();

        return redirect()->route('blog.index')->withSuccess('Blog restored');
    }
}
