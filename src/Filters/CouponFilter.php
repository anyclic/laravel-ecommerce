<?php

namespace Vtlabs\Ecommerce\Filters;

use Carbon\Carbon;
use EloquentFilter\ModelFilter;
use Illuminate\Support\Facades\App;
use Vtlabs\Core\Helpers\CoreHelper;

class CouponFilter extends ModelFilter
{
    public function setup()
    {
        if (!CoreHelper::isAdmin()) {
            $this->whereNull('expires_at')->orWhereDate('expires_at', '>=', Carbon::now());
        }
    }
}
