<?php

namespace Vtlabs\Ecommerce\Listeners;

use Vtlabs\Core\Models\User\User;
use Vtlabs\Ecommerce\Models\Order;
use Vtlabs\Payment\Events\OnWalletPayment;
use Vtlabs\Ecommerce\Http\Resources\OrderResource;

class OnWalletPaymentListener
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
     * @param  OnWalletPayment $event
     * @return void
     */
    public function handle(OnWalletPayment $event)
    {
        $payment = $event->payment;

        if ($payment->payable_type == 'Vtlabs\Ecommerce\Models\Order' && $payment->status == 'paid') {
            $order = Order::find($payment->payable_id);
            $user = User::find($payment->payer_id);

            $user->withdraw($payment->amount, 'withdraw', [
                'description' => 'Order #' . $order->id,
                'type' => 'wallet_payment',
                'source_title' => 'Order #' . $order->id,
                'source' => 'order',
                'source_id' => $order->id,
                'source_amount' => $order->price,
                'source_payment_type' => $order->payment->paymentMethod->title,
                'source_data' => new OrderResource($order)
            ]);
        }
    }
}
