<?php

namespace Vtlabs\Ecommerce\Models;

use Illuminate\Database\Eloquent\Model;

class CouponUser extends Model
{        
    protected $table = 'ecommerce_coupons_users';

    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(config('auth.models.user'));
    }

    public function coupon()
    {
        return $this->belongsTo(config('vtlabs_ecommerce.models.coupon'));
    }
}
