<?php

namespace Vtlabs\Ecommerce\Http\Controllers\Api\Admin;

use Illuminate\Http\Request;
use Vtlabs\Ecommerce\Models\Coupon;
use Illuminate\Support\Facades\Gate;
use Vtlabs\Ecommerce\Filters\CouponFilter;
use Vtlabs\Core\Http\Controllers\Controller;
use Vtlabs\Ecommerce\Http\Resources\Admin\CouponAdminResource;

class CouponController extends Controller
{
    public function index(Request $request)
    {
        $coupons = Coupon::filter($request->all(), couponFilter::class);

        return CouponAdminResource::collection($coupons->paginate());
    }

    public function store(Request $request)
    {
        $request->validate([
            'title_translations' => 'required|translation',
            'detail_translations' => 'required|translation',
            'meta' => 'sometimes|json|nullable',
            'code' => 'required',
            'reward' => 'required|numeric',
            'type' => 'required|in:fixed,percent',
            'expires_at' => 'required|date_format:Y-m-d'
        ]);

        request()->merge([
            "title" => request()->input('_title'),
            "detail" => request()->input('_detail')
        ]);

        if ($request->meta) {
            request()->merge([
                "meta" => json_decode($request->meta)
            ]);
        }

        $coupon = Coupon::create($request->only(['title', 'detail', 'meta', 'code', 'reward', 'type', 'expires_at']));

        if ($request->image) {
            $coupon->addMedia($request->image)->toMediaCollection("images");
        }

        return new CouponAdminResource($coupon->fresh());
    }

    public function show($id)
    {
        return new CouponAdminResource(Coupon::find($id));
    }

    public function update(Request $request, $id)
    {
        $coupon = Coupon::findOrFail($id);

        $request->validate([
            'title_translations' => 'required|translation',
            'detail_translations' => 'required|translation',
            'meta' => 'sometimes|json|nullable',
            'code' => 'required',
            'reward' => 'required|numeric',
            'type' => 'required|in:fixed,percent',
            'expires_at' => 'required|date_format:Y-m-d'
        ]);

        request()->merge([
            "title" => request()->input('_title'),
            "detail" => request()->input('_detail')
        ]);

        if ($request->meta) {
            request()->merge([
                "meta" => json_decode($request->meta)
            ]);
        }

        $coupon->fill($request->only(['title', 'detail', 'meta', 'code', 'reward', 'type', 'expires_at']));
        $coupon->save();

        return new CouponAdminResource($coupon->fresh());
    }

    public function destroy($id)
    {
        Gate::authorize('delete');

        Coupon::findOrFail($id)->delete();

        return response()->json([], 200);
    }
}
