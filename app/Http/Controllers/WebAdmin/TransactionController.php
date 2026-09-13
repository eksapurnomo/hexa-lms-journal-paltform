<?php

namespace App\Http\Controllers\WebAdmin;

use App\Http\Controllers\Controller;
use App\Repositories\TransactionRepository;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->cat_search ? strtolower($request->cat_search) : null;
        $transections = TransactionRepository::query()->when($search, function ($query) use ($search) {
            $query->where('payment_method', 'like', '%' . $search . '%')->whereHas('course', function ($query) use ($search) {
                $query->where('title', 'like', '%' . $search . '%');
            })->orWhereHas('user', function ($query) use ($search) {
                $query->where('name', 'like', '%' . $search . '%');
            });
        })
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return view('transaction.index', [
            'transactions' => $transections,
        ]);
    }
}
