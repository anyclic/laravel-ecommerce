<?php

namespace Vtlabs\Ecommerce\Models;

use EloquentFilter\Filterable;
use Spatie\ModelStatus\HasStatuses;
use Vtlabs\Core\Helpers\CoreHelper;
use Rennokki\Rating\Traits\CanBeRated;
use Illuminate\Database\Eloquent\Model;
use Rennokki\Rating\Contracts\Rateable;
use Spatie\Translatable\HasTranslations;
use Vtlabs\Core\Traits\CoreHasMediaTrait;
use Spatie\MediaLibrary\HasMedia\HasMedia;
use ChristianKuri\LaravelFavorite\Traits\Favoriteable;

class Product extends Model implements HasMedia, Rateable
{
    use Filterable,
        Favoriteable,
        HasStatuses,
        HasTranslations,
        CoreHasMediaTrait,
        CanBeRated;

    protected $table = 'ecommerce_products';

    protected $guarded = [];

    protected $casts = [
        'title' => 'json',
        'detail' => 'json',
        'meta' => 'json',
        'price' => 'float',
        'sells_count' => 'integer',
        'type' => 'integer',
        'gstype' => 'integer',
    ];

    protected $appends = ['mediaurls'];

    public $translatable = ['title', 'detail'];

    public static function boot()
    {
        parent::boot();

        // when product created
        static::created(function ($product) {
            $product->setStatus(config('vtlabs_ecommerce.product.status_default'));
        });
    }

    public static function updateSellsCount($vendorProductId)
    {
        try {
            $vendorProduct = VendorProduct::find($vendorProductId);
            if ($vendorProduct) {
                $product = Product::find($vendorProduct->product_id);
                $product->sells_count += $product->sells_count;
                $product->save();
            }
        } catch (\Exception $ex) {
            //
        }
    }

    public function categories()
    {
        return $this->belongsToMany(config('vtlabs_category.models.category'), 'ecommerce_product_categories');
    }

    public function addonGroups()
    {
        return $this->hasMany(config('vtlabs_ecommerce.models.addon_group'));
    }

    public function vendorProducts()
    {
        return $this->hasMany(config('vtlabs_ecommerce.models.vendor_product'));
    }

    public function isValidStatus(string $name, ?string $reason = null): bool
    {
        return in_array($name, config('vtlabs_ecommerce.product.status_list', []));
    }

    public function attachAddons($request, $translated = true)
    {
        if (!is_array($request['addon_groups'])) {
            return;
        }

        $addonGroups = $request['addon_groups'];

        // clear existing addons
        AddonGroup::where('product_id', $this->id)->delete();

        $indexGroup = 0;
        foreach ($addonGroups as $group) {
            $addonGroup = new AddonGroup();
            if (!$translated) {
                CoreHelper::fillTranslation($addonGroup, ["title" => $group['title']]);
            } else {
                $addonGroup->title = $request['_addon_groups.' . $indexGroup . '.title'];
            }
            $addonGroup->min_choices = $group['min_choices'];
            $addonGroup->max_choices = $group['max_choices'];
            $addonGroup->product_id = $this->id;
            $addonGroup->save();

            // update or create choices
            $indexChoice = 0;
            foreach ($group['choices'] as $choice) {
                $addonChoice = new AddonChoice();
                if (!$translated) {
                    CoreHelper::fillTranslation($addonChoice, ["title" => $choice['title']]);
                } else {
                    $addonChoice->title = $request['_addon_groups.' . $indexGroup . '.choices.' . $indexChoice . '.title'];
                }
                $addonChoice->price = $choice['price'];
                $addonChoice->product_addon_group_id = $addonGroup->id;
                $addonChoice->save();
                $indexChoice++;
            }
            $indexGroup++;
        }
    }
}
