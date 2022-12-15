<?php

namespace Vtlabs\Ecommerce\Models;

use EloquentFilter\Filterable;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia\HasMedia;

class VendorProduct extends Model
{
    use Filterable;
        
    protected $table = 'ecommerce_vendor_products';

    protected $guarded = [];

    protected $casts = [
        'price' => 'float',
        'sale_price' => 'float',
        'stock_quantity' => 'integer',
        'stock_low_threshold' => 'integer'
    ];

    protected $dates = [
        'sale_price_from',
        'sale_price_to'
    ];
    
    public function getSellsCountAttribute()
    {
        // TODO: calculate sales count
        return OrderProduct::where('vendor_product_id', $this->id)->count();
    }

    public function vendor()
    {
        return $this->belongsTo(config('vtlabs_ecommerce.models.vendor'));
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
