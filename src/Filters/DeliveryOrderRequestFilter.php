<?php

namespace Vtlabs\Ecommerce\Filters;

use EloquentFilter\ModelFilter;

class DeliveryOrderRequestFilter extends ModelFilter
{
    public function setup()
    {
        $this->doesntHave('order.delivery');
    }

    public function order($id)
    {
        return $this->whereHas('order', function($query) use ($id)
        {
            return $query->where('id', $id);
        });
    }

    public function delivery($id)
    {
        return $this->whereHas('delivery', function($query) use ($id)
        {
            return $query->where('id', $id);
        });
    }
}
