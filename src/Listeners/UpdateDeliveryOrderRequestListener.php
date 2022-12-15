<?php

namespace Vtlabs\Ecommerce\Listeners;

use Vtlabs\Ecommerce\Events\UpdateDeliveryOrderRequest;
use Vtlabs\Ecommerce\Events\UpdateOrder;

class UpdateDeliveryOrderRequestListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  UpdateDeliveryOrderRequest $event
     * @return void
     */
    public function handle(UpdateDeliveryOrderRequest $event)
    {
        $deliveryRequest = $event->deliveryOrderRequest;

        if($deliveryRequest->status == 'accepted') {

            // update order
            $order = $deliveryRequest->order;
            $orderDelivery = $order->delivery()->create([
                'order_id' => $deliveryRequest->order_id,
                'delivery_profile_id' => $deliveryRequest->delivery_profile_id
            ]);

            $orderDelivery->setStatus('allotted');

            // update delivery profile
            $deliveryProfile = $deliveryRequest->delivery;
            $deliveryProfile->assigned = true;
            $deliveryProfile->save();
        }
    }
}
