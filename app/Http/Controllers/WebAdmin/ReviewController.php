<?php

namespace App\Http\Controllers\WebAdmin;

use App\Http\Controllers\Controller;
use App\Models\Review;
use App\Repositories\ReviewRepository;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->cat_search ? strtolower($request->cat_search) : null;


        if (auth()->user()->hasRole('admin') || auth()->user()->is_admin) {
            $reviews = ReviewRepository::query()->when($search, function ($query) use ($search) {
                $query->where('comment', 'like', '%' . $search . '%')
                    ->OrwhereHas('course', function ($query) use ($search) {
                        $query->where('title', 'like', '%' . $search . '%');
                    })
                    ->OrwhereHas('user', function ($query) use ($search) {
                        $query->where('name', 'like', '%' . $search . '%');
                    });
            })
                ->withTrashed()
                ->latest('id')
                ->paginate(20)->withQueryString();
        } else {
            $reviews = ReviewRepository::query()
                ->whereHas('course', function ($query) {
                    $query->whereHas('instructor', function ($query) {
                        $query->whereHas('user', function ($query) {
                            $query->where('user_id', auth()->user()->id);
                        });
                    });
                })
                ->when($search, function ($query) use ($search) {
                    $query->where('comment', 'like', '%' . $search . '%')
                        ->OrwhereHas('course', function ($query) use ($search) {
                            $query->where('title', 'like', '%' . $search . '%');
                        })
                        ->OrwhereHas('user', function ($query) use ($search) {
                            $query->where('name', 'like', '%' . $search . '%');
                        });
                })
                ->withTrashed()
                ->latest('id')
                ->paginate(20)->withQueryString();
        }





        return view('review.index', [
            'reviews' => $reviews,
        ]);
    }

    public function delete(Review $review)
    {
        $review->delete();
        return redirect()->route('review.index')->withSuccess('Review removed');
    }
}
