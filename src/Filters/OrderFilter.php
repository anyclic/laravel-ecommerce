<?php

namespace Vtlabs\Ecommerce\Filters;

use Carbon\Carbon;
use EloquentFilter\ModelFilter;
use Vtlabs\Ecommerce\Models\Order;
use Illuminate\Support\Facades\App;
use Vtlabs\Core\Helpers\CoreHelper;

class OrderFilter extends ModelFilter
{
    public function setup()
    {
    }

    public function user($id)
    {
        return $this->whereHas('user', function ($query) use ($id) {
            return $query->where('name', 'like', '%' . $id . '%')
                ->orWhere('mobile_number', 'like', '%' . $id . '%')
                ->orWhere('email', 'like', '%' . $id . '%');
        });
    }

    public function currentUser($id)
    {
        return $this->whereHas('user', function ($query) use ($id) {
            return $query->where('id', $id);
        });
    }

    public function vendor($id)
    {
        if (is_numeric($id)) {
            return $this->whereHas('vendor', function ($query) use ($id) {
                return $query->where('id', $id);
            });
        } else {
            return $this->whereHas('vendor', function ($query) use ($id) {
                return CoreHelper::searchTranslated($query, 'name', App::getLocale(), $id);
            });
        }
    }

    public function deliveryProfile($id)
    {
        return $this->whereHas('delivery.delivery', function ($query) use ($id) {
            return $query->where('id', $id);
        });
    }

    public function id($id)
    {
        return $this->where('id', $id);
    }

    public function status($status)
    {
        return $this->currentStatus($status);
    }

    public function active($active)
    {
        return $this->currentStatus(['new', 'pending', 'accepted', 'preparing', 'prepared', 'dispatched']);
    }

    public function past($past)
    {
        return $this->currentStatus(array_merge(Order::COMPLETED_ORDER_STATUS, Order::FAILED_ORDER_STATUS));
    }

    public function duration($duration)
    {
        $created_at = Carbon::now()->subDays(365 * 5);

        switch ($duration) {
            case 'hours':
                $created_at = Carbon::now()->subDays(1);
                break;
            case 'days':
                $created_at = Carbon::now()->subDays(7);
                break;
            case 'months':
                $created_at = Carbon::now()->subDays(30);
                break;
            case 'years':
                $created_at = Carbon::now()->subDays(365);
                break;
        }

        return $this->whereDate('created_at', '>', $created_at)->whereDate('created_at', '<=', Carbon::now());
    }
}
