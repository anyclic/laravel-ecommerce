<?php

namespace Vtlabs\Ecommerce\Http\Controllers\Api\Admin;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Vtlabs\Ecommerce\Models\Order;
use Illuminate\Support\Facades\Gate;
use Vtlabs\Ecommerce\Filters\OrderFilter;
use Vtlabs\Ecommerce\Services\OrderService;
use Vtlabs\Core\Http\Controllers\Controller;
use Illuminate\Validation\ValidationException;
use Vtlabs\Ecommerce\Events\UpdateOrderStatus;
use Vtlabs\Ecommerce\Http\Resources\Admin\OrderAdminResource;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $orders = Order::filter($request->all(), OrderFilter::class)->ordered();

        return OrderAdminResource::collection($orders->paginate());
    }

    public function show(Order $order)
    {
        return new OrderAdminResource($order);
    }

    public function update(Order $order, Request $request)
    {
        $request->validate([
            'status' => ['required', Rule::in(config('vtlabs_ecommerce.order.status_list', []))]
        ]);

        if ($order->status !== $request->status) {
            $lastStatus = $order->status;

            if (in_array($request->status, ['dispatched', 'intransit']) && !$order->delivery) {
                $orderService = new OrderService($order);
                $orderService->sendDeliveryRequest();

                if (!$order->delivery) {
                    throw ValidationException::withMessages(['status' => 'Status cannot be updated before delivery is assigned. No nearby delivery profile found']);
                }
            }
            $order->setStatus($request->status);

            $order->save();

            event(new UpdateOrderStatus($order, 'order', $request->status, $lastStatus));
        }

        return new OrderAdminResource($order->fresh());
    }

    public function destroy($id)
    {
        Gate::authorize('delete');

        Order::findOrFail($id)->delete();

        return response()->json([], 200);
    }
}
