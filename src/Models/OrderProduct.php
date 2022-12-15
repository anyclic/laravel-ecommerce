<?php

namespace Vtlabs\Ecommerce\Models;

use Illuminate\Database\Eloquent\Model;
use EloquentFilter\Filterable;

class OrderProduct extends Model
{
    use Filterable;

    protected $table = 'ecommerce_order_products';

    protected $guarded = [];

    protected $casts = [
        'quantity' => 'integer',
        'total' => 'float'
    ];

    public function order()
    {
        return $this->belongsTo(config('vtlabs_ecommerce.models.order'));
    }

    public function vendorProduct()
    {
        return $this->belongsTo(config('vtlabs_ecommerce.models.vendor_product'));
    }

    public function addonChoices()
    {
        return $this->hasMany(OrderProductAddon::class);
    }
}
