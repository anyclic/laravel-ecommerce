<?php

namespace Vtlabs\Ecommerce\Models;

use EloquentFilter\Filterable;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia\HasMedia;
use Vtlabs\Core\Traits\CoreHasMediaTrait;

class DeliveryProfile extends Model implements HasMedia
{
    use Filterable,
        CoreHasMediaTrait;
    
    protected $table = 'ecommerce_delivery_profiles';
    
    protected $guarded = [];
    
    protected $casts = [
        'meta' => 'json',
        'is_verified' => 'integer',
        'is_online' => 'integer',
        'assigned' => 'integer',
        'longitude' => 'float',
        'latitude' => 'float'
    ];

    protected $appends = ['mediaurls'];

    public static function findByUser($userId)
    {
        return DeliveryProfile::where('user_id', $userId)->firstOrFail();
    }

    public function user()
    {
        return $this->belongsTo(config('auth.models.user'));
    }
}
