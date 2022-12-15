<?php

namespace Vtlabs\Ecommerce\Models;

use EloquentFilter\Filterable;
use Spatie\ModelStatus\HasStatuses;
use Illuminate\Database\Eloquent\Model;

class DeliveryOrderRequest extends Model
{
    use Filterable,
        HasStatuses;
    
    protected $table = 'ecommerce_delivery_order_requests';
    
    protected $guarded = [];
    
    protected $casts = [
        'meta' => 'json'
    ];

    public static function boot()
    {
        parent::boot();

        // on create
        static::created(function ($request) {
            $request->setStatus(config('vtlabs_ecommerce.delivery_order_request.status_default'));
        });
    }

    public function isValidStatus(string $name, ?string $reason = null): bool
    {
        return in_array($name, config('vtlabs_ecommerce.order.status_list', []));
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('created_at', 'desc');
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function delivery()
    {
        return $this->belongsTo(DeliveryProfile::class, 'delivery_profile_id');
    }
}
