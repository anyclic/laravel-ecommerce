<?php

namespace Vtlabs\Ecommerce\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\ModelStatus\HasStatuses;
use EloquentFilter\Filterable;

class OrderDelivery extends Model
{
    use Filterable, HasStatuses;

    protected $table = 'ecommerce_order_deliveries';

    protected $guarded = [];

    protected $casts = [];

    public static function boot()
    {
        parent::boot();

        // on create
        static::created(function ($vendor) {
            $vendor->setStatus(config('vtlabs_ecommerce.order_delivery.status_default'));
        });
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('created_at', 'desc');
    }

    public function isValidStatus(string $name, ?string $reason = null): bool
    {
        return in_array($name, config('vtlabs_ecommerce.order_delivery.status_list', []));
    }

    public function order()
    {
        return $this->hasOne(config('vtlabs_ecommerce.models.order'));
    }

    public function delivery()
    {
        return $this->belongsTo(DeliveryProfile::class, 'delivery_profile_id');
    }
}
