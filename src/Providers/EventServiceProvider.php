<?php

namespace Vtlabs\Ecommerce\Providers;

use Vtlabs\Core\Events\LoggedIn;
use Vtlabs\Core\Events\RoleAssigned;
use Vtlabs\Ecommerce\Events\NewOrder;
use Vtlabs\Ecommerce\Events\UpdateOrder;
use Vtlabs\Payment\Events\PaymentUpdated;
use Vtlabs\Payment\Events\OnWalletPayment;
use Vtlabs\Ecommerce\Events\UpdateOrderStatus;
use Vtlabs\Ecommerce\Listeners\LoggedInListener;
use Vtlabs\Ecommerce\Listeners\NewOrderListener;
use Vtlabs\Ecommerce\Listeners\UpdateOrderListener;
use Vtlabs\Ecommerce\Listeners\RoleAssignedListener;
use Vtlabs\Ecommerce\Listeners\PaymentUpdatedListener;
use Vtlabs\Ecommerce\Events\UpdateDeliveryOrderRequest;
use Vtlabs\Ecommerce\Listeners\OnWalletPaymentListener;
use Vtlabs\Ecommerce\Listeners\UpdateOrderStatusListener;
use Vtlabs\Ecommerce\Listeners\UpdateDeliveryOrderRequestListener;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        LoggedIn::class => [LoggedInListener::class],
        RoleAssigned::class => [RoleAssignedListener::class],
        NewOrder::class => [NewOrderListener::class],
        UpdateOrder::class => [UpdateOrderListener::class],
        UpdateDeliveryOrderRequest::class => [UpdateDeliveryOrderRequestListener::class],
        UpdateOrderStatus::class => [UpdateOrderStatusListener::class],
        OnWalletPayment::class => [OnWalletPaymentListener::class],
        PaymentUpdated::class => [PaymentUpdatedListener::class]
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        //
    }
}
