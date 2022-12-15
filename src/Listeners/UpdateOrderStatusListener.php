<?php

namespace Vtlabs\Ecommerce\Listeners;

use Vtlabs\Core\Models\Setting;
use Vtlabs\Core\Models\User\User;
use Vtlabs\Ecommerce\Models\Order;
use Vtlabs\Ecommerce\Services\OrderService;
use Vtlabs\Ecommerce\Events\UpdateOrderStatus;

class UpdateOrderStatusListener
{
    private $order;

    private $status;

    private $statusType;

    private $orderService;

    private $deliveryProfile;
    
    private $lastStatus;

    public function __construct()
    {
        //
    }

    public function handle(UpdateOrderStatus $event)
    {
        $this->order = $event->order;
        $this->status = $event->status;
        $this->lastStatus = $event->lastStatus;
        $this->statusType = $event->statusType;
        $this->orderService = new OrderService($this->order);
        $this->deliveryProfile = $this->order->delivery->delivery ?? null;

        if ($this->statusType == 'order') {
            $this->_onOrderStatusUpdate();
        }

        if ($this->statusType == 'delivery') {
            $this->_onOrderDeliveryStatusUpdate();
        }

        $orderService = new OrderService($this->order);
        $orderService->notifyFirebase();
    }

    private function _onOrderStatusUpdate()
    {
        if (in_array($this->status, Order::ACTIVE_ORDER_STATUS)) {

            // try assigning delivery profile to order
            if (!$this->order->delivery) {
                $this->orderService->sendDeliveryRequest();
            }
        }

        if ($this->status == 'complete') {
            $this->_onOrderComplete();
        }

        $this->notifyStatusUpdate();
    }

    private function _onOrderDeliveryStatusUpdate()
    {
        switch ($this->status) {
            case 'started':
                $this->order->setStatus('intransit');
                break;
            case 'complete':
                $this->order->setStatus('complete');
                $this->_onOrderComplete();
                $this->notifyStatusUpdate();
                break;
        }
    }

    private function _onOrderComplete()
    {
        if ($this->deliveryProfile) {
            // un assign delivery profile
            $this->deliveryProfile->assigned = false;
            $this->deliveryProfile->save();
        }

        // handle payment
        if ($this->order->payment->paymentMethod->type == 'postpaid') {
            $this->order->payment->setStatus('paid');
            $this->order->payment->save();
        }

        $this->_distributeEarnings();
    }

    private function _distributeEarnings()
    {
        $adminTotalEarning = 0;
        $vendorEarnings = $this->order->subtotal;

        # vendor earnings
        $adminShareInOrder = floatval(Setting::findByKey('admin_commision_value'));
        $vendorEarnings = $this->order->subtotal - ($this->order->subtotal * $adminShareInOrder) / 100;
        $adminTotalEarning = ($this->order->subtotal * $adminShareInOrder) / 100; // admin gets it's share of earning


        $this->order->vendor->user->deposit($vendorEarnings, 'deposit', [
            'description' => 'Order #' . $this->order->id,
            'type' => 'earnings',
            'source' => 'order',
            'source_id' => $this->order->id,
            'source_amount' => $this->order->total,
            'source_payment_type' => $this->order->payment->paymentMethod->type == 'postpaid' ? 'COD' : 'Online'
        ]);

        # delivery profile earnings
        if ($this->deliveryProfile) {
            $deliveryFee = $this->order->delivery_fee;

            $this->deliveryProfile->user->deposit(
                $deliveryFee,
                'deposit',
                [
                    'description' => 'Order #' . $this->order->id,
                    'type' => 'earnings',
                    'source_title' => 'Earning',
                    'source' => 'order',
                    'source_id' => $this->order->id,
                    'source_amount' => $this->order->total,
                    'source_payment_type' => $this->order->payment->paymentMethod->type == 'postpaid' ? 'COD' : 'Online'
                ]
            );

            if ($this->order->payment->paymentMethod->slug == 'cod') {
                $this->deliveryProfile->user->forceWithdraw(
                    $this->order->total,
                    'withdraw',
                    [
                        'description' => 'Cash collected',
                        'type' => 'cash_collected',
                        'source' => 'order',
                        'source_id' => $this->order->id,
                        'source_amount' => $this->order->total,
                        'source_payment_type' => 'COD'
                    ]
                );
            }
        }

        // if admin has earned something from this order, create a transaction for admin's earning
        if ($adminTotalEarning !== 0) {
            User::find(1)->deposit($adminTotalEarning, 'deposit', [
                'description' => 'Order #' . $this->order->id,
                'type' => 'earnings',
                'source' => 'order',
                'source_id' => $this->order->id,
                'source_amount' => $this->order->total,
                'source_payment_type' => $this->order->payment->paymentMethod->type == 'postpaid' ? 'COD' : 'Online'
            ]);
        }
    }

    private function notifyStatusUpdate()
    {
        if($this->status == $this->lastStatus) {
            return;
        }

        if ($this->status == 'accepted') {
            $this->order->user->sendPushNotification(
                'customer',
                __('vtlabs_ecommerce::messages.notification_accept_order_user_title', ['id' => $this->order->id]),
                __('vtlabs_ecommerce::messages.notification_accept_order_user_body')
            );
        }

        if ($this->status == 'dispatched') {
            $this->order->user->sendPushNotification(
                'customer',
                __('vtlabs_ecommerce::messages.notification_dispatched_order_user_title', ['id' => $this->order->id]),
                __('vtlabs_ecommerce::messages.notification_dispatched_order_user_body')
            );
        }

        if ($this->status == 'complete') {
            $this->order->user->sendPushNotification(
                'customer',
                __('vtlabs_ecommerce::messages.notification_complete_order_user_title', ['id' => $this->order->id]),
                __('vtlabs_ecommerce::messages.notification_complete_order_user_body')
            );

            $this->order->vendor->user->sendPushNotification(
                'vendor',
                __('vtlabs_ecommerce::messages.notification_complete_order_vendor_title', ['id' => $this->order->id]),
                __('vtlabs_ecommerce::messages.notification_complete_order_vendor_body')
            );

            if ($this->deliveryProfile) {
                $this->deliveryProfile->user->sendPushNotification(
                    'delivery',
                    __('vtlabs_ecommerce::messages.notification_complete_order_delivery_title', ['id' => $this->order->id]),
                    __('vtlabs_ecommerce::messages.notification_complete_order_delivery_body')
                );
            }
        }
    }
}
