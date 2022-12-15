<?php

namespace Vtlabs\Ecommerce\Listeners;

use Vtlabs\Ecommerce\Events\UpdateOrder;

class UpdateOrderListener
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
     * @param  UpdateOrder $event
     * @return void
     */
    public function handle(UpdateOrder $event)
    {
    }
}
