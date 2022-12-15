<?php

namespace Vtlabs\Ecommerce\Models;

use EloquentFilter\Filterable;
use Spatie\ModelStatus\HasStatuses;
use Rennokki\Rating\Traits\CanBeRated;
use Illuminate\Database\Eloquent\Model;
use Rennokki\Rating\Contracts\Rateable;
use Spatie\Translatable\HasTranslations;
use Vtlabs\Core\Traits\CoreHasMediaTrait;
use Spatie\MediaLibrary\HasMedia\HasMedia;
use ChristianKuri\LaravelFavorite\Traits\Favoriteable;

class Vendor extends Model implements HasMedia, Rateable
{
    use Filterable,
        Favoriteable,
        HasStatuses,
        HasTranslations,
        CoreHasMediaTrait,
        CanBeRated;
    
    protected $table = 'ecommerce_vendors';
    
    protected $guarded = [];
    
    protected $casts = [
        'name' => 'json',
        'details' => 'json',
        'tagline' => 'json',
        'meta' => 'json',
        'minimum_order' => 'integer',
        'delivery_fee' => 'float',
        'longitude' => 'float',
        'latitude' => 'float',
        'orders_count' => 'integer',
        'average_ratings' => 'float'
    ];

    protected $appends = ['mediaurls'];
    
    public $translatable = ['name', 'details', 'tagline'];

    public static function boot() {
        parent::boot();

        // on create
        static::created(function ($vendor) {
            $vendor->setStatus(config('vtlabs_ecommerce.vendor.status_default'));
        });

    }

    public static function findByUser($userId)
    {
        return Vendor::where('user_id', $userId)->firstOrFail();
    }
    
    public function categories()
    {
        return $this->belongsToMany(config('vtlabs_category.models.category'), 'ecommerce_vendor_categories');
    }

    public function productCategories()
    {
        return $this->belongsToMany(config('vtlabs_category.models.category'), 'ecommerce_vendor_product_categories')->distinct();
    }

    public function user()
    {
        return $this->belongsTo(config('auth.models.user'));
    }
    
    public function isValidStatus(string $name, ?string $reason = null): bool
    {
        return in_array($name, config('vtlabs_ecommerce.vendor.status_list', []));
    }
}
