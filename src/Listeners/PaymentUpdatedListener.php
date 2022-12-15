<?php

namespace Vtlabs\Ecommerce\Listeners;

use Vtlabs\Ecommerce\Models\Order;
use Vtlabs\Ecommerce\Events\NewOrder;
use Vtlabs\Payment\Events\PaymentUpdated;

class PaymentUpdatedListener
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
     * @param  Registered $event
     * @return void
     */
    public function handle(PaymentUpdated $event)
    {
        $payment = $event->payment;
        
        // we need to process the order according to payment status
        if ($payment->payable_type == 'Vtlabs\Ecommerce\Models\Order') {
            if($payment->status == 'paid') {
                $order = Order::find($payment->payable_id);
                event(new NewOrder($order->fresh()));
            }

            if($payment->status == 'failed') {
                $order = Order::find($payment->payable_id);
                $order->setStatus('cancelled');
                $order->save();
            }
        }
        return true;
    }
}
