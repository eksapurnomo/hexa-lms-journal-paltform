<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\CouponStoreRequest;
use App\Http\Requests\CouponUpdateRequest;
use App\Http\Resources\CouponResource;
use App\Models\Coupon;
use App\Repositories\CouponRepository;
use Illuminate\Http\Request;

class CouponController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $perPage = $request->input('items_per_page', 10);
        $pageNumber = $request->input('page_number', 1);
        $skip = ($pageNumber - 1) * $perPage;

        $coupons = Coupon::all()->skip($skip)->take($perPage)->values();

        return $this->json($coupons ? 'Coupons found' : 'No coupons found', [
            'total_items' => count($coupons),
            'coupons' => CouponResource::collection($coupons),
        ], $coupons ? 200 : 404);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CouponStoreRequest $request)
    {
        $coupon = CouponRepository::storeByRequest($request);

        return $this->json('Coupon created successfully', [
            'coupon' => CouponResource::make($coupon)
        ], 201);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(CouponUpdateRequest $request, Coupon $coupon)
    {
        CouponRepository::updateByRequest($request, $coupon);
        $updatedCoupon = CouponRepository::find($coupon->id);

        return $this->json('Coupon updated successfully', [
            'coupon' => CouponResource::make($updatedCoupon)
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Coupon $coupon)
    {
        $coupon->delete();

        return $this->json('Coupon deleted successfully');
    }
}
