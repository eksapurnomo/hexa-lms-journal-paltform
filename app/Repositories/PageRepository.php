<?php

namespace App\Repositories;

use Abedin\Maker\Repositories\Repository;
use App\Http\Requests\PageUpdateRequest;
use App\Models\Page;

class PageRepository extends Repository
{
    public static function model()
    {
        return Page::class;
    }

    public static function updateByRequest(PageUpdateRequest $request, Page $page)
    {
        return self::update($page, [
            'title' => $request->title ?? $page->title,
            // 'slug' => $request->title ? strtolower(str_replace(' ', '_', $request->title)) : $page->title,
            'content' => $request->content ?? $page->content
        ]);
    }
}
