<?php

namespace App\Http\Controllers\WebAdmin;

use App\Http\Controllers\Controller;
use App\Http\Requests\CouponStoreRequest;
use App\Http\Requests\CouponUpdateRequest;
use App\Models\Coupon;
use App\Repositories\CouponRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CouponController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->cat_search ? strtolower($request->cat_search) : null;

        $coupons = CouponRepository::query()->when($search, function ($query) use ($search) {
            $query->where('code', 'like', '%' . $search . '%');
        })
            ->latest('id')->paginate(8)->withQueryString();

        return view('coupon.index', [
            'coupons' => $coupons,
        ]);
    }

    public function create()
    {
        return view('coupon.create');
    }

    public function store(CouponStoreRequest $request)
    {
        CouponRepository::storeByRequest($request);

        return to_route('coupon.index')->with('success', 'Coupon created');
    }

    public function edit(Coupon $coupon)
    {
        return view('coupon.edit', [
            'coupon' => $coupon,
        ]);
    }

    public function update(CouponUpdateRequest $request, Coupon $coupon)
    {
        CouponRepository::updateByRequest($request, $coupon);

        return to_route('coupon.index')->withSuccess('Coupon updated');
    }

    public function delete(int $id)
    {
        CouponRepository::find($id)->delete();

        return redirect()->route('coupon.index')->withSuccess('Coupon deleted');
    }
}
