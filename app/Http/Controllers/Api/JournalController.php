<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\JournalResource;
use App\Models\Journal;
use Illuminate\Http\Request;

class JournalController extends Controller
{
    /**
     * Display a listing of active journals.
     */
    public function index(Request $request)
    {
        $query = Journal::where('status', 'active');

        // Search by title if provided
        if ($request->has('search')) {
            $searchTerm = $request->input('search');
            $query->where('title', 'like', '%' . $searchTerm . '%');
        }

        // Pagination following the application's convention
        $totalItems = $query->count();
        $perPage = $request->input('items_per_page', 15);
        $pageNumber = $request->input('page_number', 1);
        $skip = ($pageNumber - 1) * $perPage;

        $journals = $query->orderBy('title', 'asc')
            ->skip($skip)
            ->take($perPage)
            ->get();

        return $this->json($journals->isNotEmpty() ? 'Journals found' : 'No journals found', [
            'total_journals' => $totalItems,
            'journals' => JournalResource::collection($journals),
        ], $journals->isNotEmpty() ? 200 : 404);
    }

    /**
     * Display the specified active journal.
     */
    public function show($slug)
    {
        $journal = Journal::where('slug', $slug)
            ->where('status', 'active')
            ->firstOrFail();

        return $this->json('Journal found', [
            'journal' => JournalResource::make($journal),
        ], 200);
    }
}
