<?php

namespace App\Http\Controllers\WebAdmin;

use App\Http\Controllers\Controller;
use App\Http\Requests\PageUpdateRequest;
use App\Models\Page;
use App\Repositories\PageRepository;

class PageController extends Controller
{
    public function index()
    {
        return view('page.index', [
            'pages' => PageRepository::query()->latest('id')->get(),
        ]);
    }

    public function edit(Page $page)
    {
        return view('page.edit', [
            'page' => $page,
        ]);
    }

    public function update(PageUpdateRequest $request, Page $page)
    {
        PageRepository::updateByRequest($request, $page);

        return to_route('page.index')->withSuccess('Page updated');
    }
}
