<?php

namespace Vtlabs\Ecommerce\Models;

use Illuminate\Database\Eloquent\Model;

class OrderProductAddon extends Model
{
    protected $table = 'ecommerce_order_product_addons';

    protected $guarded = [];

    protected $casts = [
        'total' => 'float'
    ];

    public function addonChoice()
    {
        return $this->belongsTo(config('vtlabs_ecommerce.models.addon_choice'), 'product_addon_choice_id');
    }

    public function order()
    {
        return $this->belongsTo(config('vtlabs_ecommerce.models.order'));
    }
}
