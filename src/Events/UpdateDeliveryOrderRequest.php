<?php

namespace Vtlabs\Ecommerce\Events;

use Vtlabs\Ecommerce\Models\Order;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use Vtlabs\Ecommerce\Models\DeliveryOrderRequest;

class UpdateDeliveryOrderRequest
{
    use Dispatchable, SerializesModels;

    public $deliveryOrderRequest;

    /**
     * Create a new event instance.
     */
    public function __construct(DeliveryOrderRequest $deliveryRequest)
    {
        $this->deliveryOrderRequest = $deliveryRequest;
    }
}