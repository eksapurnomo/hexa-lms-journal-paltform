<?php

namespace App\Repositories;

use Abedin\Maker\Repositories\Repository;
use App\Http\Requests\CouponStoreRequest;
use App\Http\Requests\CouponUpdateRequest;
use App\Models\Coupon;

class CouponRepository extends Repository
{
    public static function model()
    {
        return Coupon::class;
    }

    public static function storeByRequest(CouponStoreRequest $request)
    {
        $isActive = false;

        if (isset($request->is_active)) {
            $isActive = $request->is_active == 'on' ? true : false;
        }

        return self::create([
            'code' => $request->code,
            'discount' => $request->discount,
            'is_active' => $isActive,
            'applicable_from' => $request->applicable_from,
            'valid_until' => $request->valid_until
        ]);
    }

    public static function updateByRequest(CouponUpdateRequest $request, Coupon $coupon)
    {
        $isActive = false;

        if (isset($request->is_active)) {
            $isActive = $request->is_active == 'on' ? true : false;
        }

        return self::update($coupon, [
            'code' => $request->code ?? $coupon->code,
            'discount' => $request->discount ?? $coupon->discount,
            'applicable_from' => $request->applicable_from ?? $coupon->applicable_from,
            'valid_until' => $request->valid_until ?? $coupon->valid_until,
            'is_active' => $isActive,
        ]);
    }
}
