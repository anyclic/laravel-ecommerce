<?php

namespace Vtlabs\Ecommerce\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Vtlabs\Ecommerce\Models\Order;
use Vtlabs\Ecommerce\Models\DeliveryProfile;
use Vtlabs\Ecommerce\Jobs\FirebaseUpdateOrder;
use Vtlabs\Ecommerce\Jobs\FirebaseUpdateDelivery;
use Vtlabs\Ecommerce\Models\DeliveryOrderRequest;
use Vtlabs\Ecommerce\Http\Resources\OrderResource;
use Vtlabs\Ecommerce\Filters\DeliveryProfileFilter;
use Vtlabs\Ecommerce\Http\Resources\DeliveryOrderRequestResource;

class OrderService
{
    private $order;

    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    public function sendDeliveryRequest()
    {
        $deliveryProfiles = $this->nearbyDeliveryProfiles()->get();

        foreach ($deliveryProfiles as $deliveryProfile) {
            $deliveryRequest = DeliveryOrderRequest::create([
                'delivery_profile_id' => $deliveryProfile->id,
                'order_id' => $this->order->id
            ]);

            $deliveryProfile->user->sendPushNotification(
                'delivery',
                __('vtlabs_ecommerce::messages.notification_new_order_delivery_title', ['id' => $this->order->id]),
                __('vtlabs_ecommerce::messages.notification_new_order_delivery_body')
            );

            FirebaseUpdateDelivery::dispatch($deliveryProfile, $deliveryRequest);
        }
    }

    public function nearbyDeliveryProfiles()
    {
        $deliveryProfiles = DeliveryProfile::filter([
            'vendor' => $this->order->vendor->id
        ], DeliveryProfileFilter::class);

        // fiter out delivery profiles to whom request already sent
        $deliveryProfileIds = DeliveryOrderRequest::select('delivery_profile_id')
            ->where(function ($query) {
                // TODO: check for more conditions
                $query->where('order_id', $this->order->id);
            })->get()
            ->pluck('delivery_profile_id');
        $deliveryProfiles = $deliveryProfiles->whereNotIn('id', $deliveryProfileIds);

        return $deliveryProfiles;
    }

    public function notifyFirebase()
    {
        FirebaseUpdateOrder::dispatch($this->order->id);
    }

    public static function summarize($query, $duration, $limit)
    {
        if ($duration == 'hours') {
            $query = $query->select(DB::raw('HOUR(created_at) as period'), DB::raw('count(*) as total'))
                ->whereDate('created_at', '>', Carbon::now()->subHours($limit))
                ->whereDate('created_at', '<=', Carbon::now())
                ->groupBy(DB::raw('HOUR(created_at)'));
        }

        if ($duration == 'days') {
            $query = $query->select(DB::raw('DATE(created_at) as period'), DB::raw('count(*) as total'))
                ->whereDate('created_at', '>', Carbon::now()->subDays($limit))
                ->whereDate('created_at', '<=', Carbon::now())
                ->groupBy(DB::raw('DATE(created_at)'));
        }

        if ($duration == 'months') {
            $query = $query->select(DB::raw('MONTH(created_at) as period'), DB::raw('count(*) as total'))
                ->whereDate('created_at', '>', Carbon::now()->subDays($limit * 30))
                ->whereDate('created_at', '<=', Carbon::now())
                ->groupBy(DB::raw('MONTH(created_at)'));
        }

        if ($duration == 'years') {
            $query = $query->select(DB::raw('YEAR(created_at) as period'), DB::raw('count(*) as total'))
                ->whereDate('created_at', '>', Carbon::now()->subDays($limit * 365))
                ->whereDate('created_at', '<=', Carbon::now())
                ->groupBy(DB::raw('YEAR(created_at)'));
        }

        return $query;
    }
}
