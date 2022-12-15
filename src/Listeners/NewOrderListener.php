<?php

namespace Vtlabs\Ecommerce\Listeners;

use Vtlabs\Ecommerce\Events\NewOrder;
use Vtlabs\Ecommerce\Services\OrderService;

class NewOrderListener
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
     * @param  NewOrder $event
     * @return void
     */
    public function handle(NewOrder $event)
    {
        $order = $event->order;

        $orderService = new OrderService($order);

        // $orderService->sendDeliveryRequest();

        $orderService->notifyFirebase();

        $order->vendor->user->sendPushNotification('vendor',
            __('vtlabs_ecommerce::messages.notification_new_order_vendor_title', ['id' => $order->id]),
            __('vtlabs_ecommerce::messages.notification_new_order_vendor_body')
        );
    }
}
