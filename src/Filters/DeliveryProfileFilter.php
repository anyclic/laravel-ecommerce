<?php

namespace Vtlabs\Ecommerce\Filters;

use Carbon\Carbon;
use EloquentFilter\ModelFilter;
use Illuminate\Support\Facades\App;
use Vtlabs\Core\Helpers\CoreHelper;
use Vtlabs\Ecommerce\Models\Vendor;
use Illuminate\Support\Facades\Auth;
use Vtlabs\Ecommerce\Models\DeliveryProfile;

class DeliveryProfileFilter extends ModelFilter
{
    public function setup()
    {
        if (!CoreHelper::isAdmin()) {
            $this->where('is_online', true)->where('assigned', false)->where('is_verified', true);
        }
    }

    public function vendor($id)
    {
        $vendor = Vendor::find($id);
        $distanceDelta = 8000;

        $subqueryDistance = "*, ST_Distance_Sphere(Point(ecommerce_delivery_profiles.longitude,"
            . " ecommerce_delivery_profiles.latitude),"
            . " Point($vendor->longitude, $vendor->latitude ))"
            . " as distance";

        return $this->selectRaw($subqueryDistance)->havingRaw('distance < ' . $distanceDelta);
    }

    public function user($user)
    {
        return $this->whereHas('user', function ($query) use ($user) {
            return $query->where('name', 'like', '%' . $user . '%')
                ->orWhere('mobile_number', 'like', '%' . $user . '%')
                ->orWhere('email', 'like', '%' . $user . '%')
                ->orWhere('id', $user);
        });
    }

    public function owned_by_me($owner)
    {
        if(Auth::check() && !CoreHelper::isAdmin()) {
            $this->whereJsonContains('meta->owned_by', Auth::id());
        }
    }
}
