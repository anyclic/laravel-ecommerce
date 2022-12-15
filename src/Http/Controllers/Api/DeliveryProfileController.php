<?php

namespace Vtlabs\Ecommerce\Http\Controllers\Api;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Depsimon\Wallet\Transaction;
use Illuminate\Support\Facades\DB;
use Vtlabs\Ecommerce\Models\Order;
use Illuminate\Support\Facades\Auth;
use Vtlabs\Ecommerce\Filters\OrderFilter;
use Vtlabs\Ecommerce\Models\DeliveryMode;
use Vtlabs\Ecommerce\Models\OrderDelivery;
use Vtlabs\Ecommerce\Services\OrderService;
use Vtlabs\Core\Http\Controllers\Controller;
use Vtlabs\Ecommerce\Models\DeliveryProfile;
use Vtlabs\Ecommerce\Filters\DeliveryModeFilter;
use Vtlabs\Ecommerce\Models\DeliveryOrderRequest;
use Vtlabs\Ecommerce\Http\Resources\OrderResource;
use Vtlabs\Ecommerce\Events\UpdateDeliveryOrderRequest;
use Vtlabs\Ecommerce\Filters\DeliveryOrderRequestFilter;
use Vtlabs\Ecommerce\Http\Resources\DeliveryModeResource;
use Vtlabs\Ecommerce\Http\Resources\OrderDeliveryResource;
use Vtlabs\Ecommerce\Http\Resources\DeliveryProfileResource;
use Vtlabs\Ecommerce\Http\Resources\DeliveryOrderRequestResource;

class DeliveryProfileController extends Controller
{
    public function __construct()
    {
    }

    public function showMyProfile(Request $request)
    {
        $delivery = DeliveryProfile::findByUser(Auth::user()->id);
        return new DeliveryProfileResource($delivery);
    }

    public function update(DeliveryProfile $delivery, Request $request)
    {
        $request->validate([
            'meta' => 'sometimes|json',
            'is_online' => 'sometimes|boolean',
            'assigned' => 'sometimes|boolean',
            'longitude' => 'sometimes|numeric|min:-180|max:180',
            'latitude' => 'sometimes|numeric|min:-90|max:90'
        ]);

        if ($request->meta) {
            request()->merge([
                "meta" => json_decode($request->meta)
            ]);
        }

        $delivery->fill($request->only([
            'meta', 'is_online', 'longitude', 'latitude', 'assigned'
        ]));

        $delivery->save();

        return new DeliveryProfileResource($delivery->fresh());
    }

    public function summary(DeliveryProfile $delivery, Request $request)
    {
        $request->validate([
            'duration' => 'required|in:hours,days,months,years',
            'limit' => 'required|numeric'
        ]);

        $orders = Order::whereHas('delivery', function ($query) use ($delivery) {
            $query->where('delivery_profile_id', $delivery->id);
        });

        $ordersForCount = clone $orders;

        $ordersForDistance = clone $orders;

        $orders = OrderService::summarize($orders, $request->duration, $request->limit);

        $totalEarnings = Transaction::where('wallet_id', Auth::user()->wallet->id)->where('type', 'deposit')->whereDate('created_at', '<=', Carbon::now());

        if ($request->duration == 'hours') {
            $totalEarnings = $totalEarnings->whereDate('created_at', '>', Carbon::now()->subDays(1));
        }

        if ($request->duration == 'days') {
            $totalEarnings = $totalEarnings->whereDate('created_at', '>', Carbon::now()->subDays(7));
        }

        if ($request->duration == 'months') {
            $totalEarnings = $totalEarnings->whereDate('created_at', '>', Carbon::now()->subDays(30));
        }

        if ($request->duration == 'years') {
            $totalEarnings = $totalEarnings->whereDate('created_at', '>', Carbon::now()->subDays(365));
        }

        return response([
            "orders_count" => $ordersForCount->filter(['duration' => $request->duration], OrderFilter::class)->count(),
            "distance_travelled" => $ordersForDistance->filter(['duration' => $request->duration], OrderFilter::class)->sum('distance_travelled'),
            "earnings" => $totalEarnings->sum('amount'),
            'orders_chart_data' => $orders->get()
        ]);
    }

    public function currentOrder(DeliveryProfile $delivery, Request $request)
    {
        $orders = Order::filter(['delivery_profile' => $delivery->id], OrderFilter::class);

        $orders = $orders->otherCurrentStatus('complete');

        $order = $orders->ordered()->firstOrFail();

        return new OrderResource($order);
    }

    public function deliveryRequest(DeliveryProfile $delivery, Request $request)
    {
        $request->validate([
            'order' => 'sometimes|exists:ecommerce_orders,id',
            'status' => ['sometimes', Rule::in(config('vtlabs_ecommerce.order.status_list', []))]
        ]);

        request()->merge([
            "delivery" => $delivery->id
        ]);

        $deliveryRequests = DeliveryOrderRequest::filter($request->all(), DeliveryOrderRequestFilter::class);

        $status = $request->status ?? 'pending';

        $deliveryRequests = $deliveryRequests->currentStatus([$status]);

        $deliveryRequest = $deliveryRequests->ordered()->firstOrFail();

        return new DeliveryOrderRequestResource($deliveryRequest);
    }

    public function updateDeliveryRequest(DeliveryOrderRequest $deliveryRequest, Request $request)
    {
        $request->validate([
            'status' => ['required', Rule::in(config('vtlabs_ecommerce.order.status_list', []))]
        ]);

        $deliveryRequest->setStatus($request->status);

        $deliveryRequest->save();

        event(new UpdateDeliveryOrderRequest($deliveryRequest));

        return new DeliveryOrderRequestResource($deliveryRequest);
    }

    public function deliveryModes(Request $request) 
    {
        $deliveryModes = DeliveryMode::filter($request->all(), DeliveryModeFilter::class);

        return DeliveryModeResource::collection($deliveryModes->paginate());
    }
}
