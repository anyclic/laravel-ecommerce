<?php

namespace Vtlabs\Ecommerce\Events;

use Vtlabs\Ecommerce\Models\Order;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;

class NewOrder
{
    use Dispatchable, SerializesModels;

    public $order;

    public $statusUpdate;

    /**
     * Create a new event instance.
     */
    public function __construct(Order $order)
    {
        $this->order = $order;
    }
}