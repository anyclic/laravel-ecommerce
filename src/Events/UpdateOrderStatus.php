<?php

namespace Vtlabs\Ecommerce\Events;

use Vtlabs\Ecommerce\Models\Order;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;

class UpdateOrderStatus
{
    use Dispatchable, SerializesModels;

    public $order;

    public $status;

    public $statusType;

    public $lastStatus;

    public function __construct(Order $order, $statusType, $status, $lastStatus)
    {
        $this->order = $order;
        $this->status = $status;
        $this->lastStatus = $lastStatus;
        $this->statusType = $statusType;
    }
}