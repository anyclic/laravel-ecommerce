<?php

namespace Vtlabs\Ecommerce\Listeners;

use Vtlabs\Core\Events\RoleAssigned;
use Vtlabs\Ecommerce\Models\DeliveryProfile;

class RoleAssignedListener
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
     * @param  RoleAssigned $event
     * @return void
     */
    public function handle(RoleAssigned $event)
    {
        if(in_array('vendor', $event->roles)) {
            if(!config('vtlabs_ecommerce.models.vendor')::where('user_id', $event->user->id)->exists()) {
                config('vtlabs_ecommerce.models.vendor')::create([
                    'user_id' => $event->user->id
                ]);
            }
        }

        if(in_array('delivery', $event->roles)) {
            if(!DeliveryProfile::where('user_id', $event->user->id)->exists()) {
                DeliveryProfile::create([
                    'user_id' => $event->user->id
                ]);
            }
        }
    }
}
