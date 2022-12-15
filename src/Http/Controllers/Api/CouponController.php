<?php

namespace Vtlabs\Ecommerce\Http\Controllers\Api;

use Illuminate\Http\Request;
use Vtlabs\Ecommerce\Models\Coupon;
use Illuminate\Support\Facades\Auth;
use Vtlabs\Ecommerce\Filters\CouponFilter;
use Vtlabs\Core\Http\Controllers\Controller;
use Vtlabs\Ecommerce\Http\Resources\CouponResource;

class CouponController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth')->only(['checkValidity']);
    }

    public function index(Request $request)
    {
        $coupons = Coupon::filter($request->all(), CouponFilter::class);
        return CouponResource::collection($coupons->get());
    }

    public function checkValidity(Request $request)
    {
        $request->validate([
            'code' => 'required'
        ]);

        try {
            $coupon = Coupon::getCouponFromCode($request->code, Auth::user());
            return new CouponResource($coupon);
        } catch (\Exception $ex) {
            return response()->json(['message' => $ex->getMessage()], 400);
        }
    }
}
