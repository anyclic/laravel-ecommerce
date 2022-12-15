<?php

namespace Vtlabs\Ecommerce\Filters;

use EloquentFilter\ModelFilter;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use Vtlabs\Core\Helpers\CoreHelper;
use Vtlabs\Ecommerce\Models\Vendor;
use Illuminate\Support\Facades\Auth;

class ProductFilter extends ModelFilter
{
    public function setup()
    {
        if (!CoreHelper::isAdmin()) {
            $requestByVendor = false;
            if ($this->input('vendor') && Auth::check()) {
                // we check if current request is being made by same vendor who is owner of these products
                // i.e vendor can see all his products but others user can only see products if vendor
                // is verified
                $vendor = Vendor::where('user_id', Auth::id())->first();
                if ($vendor && $vendor->id == $this->input('vendor')) {
                    $requestByVendor = true;
                }
            }

            // filter products of non verified vendors
            if (!$requestByVendor) {
                return $this->whereHas('vendorProducts.vendor', function ($query) {
                    return $query->where('is_verified', true);
                });
            }
        }

        // filter products based on user location
        // @TODO: read distance value from settings
        if ($this->input('long') && $this->input('lat')) {
            $this->whereHas('vendorProducts.vendor', function ($query) {
                $subqueryDistanceWhere = "ST_Distance_Sphere(Point(longitude,"
                    . " latitude),"
                    . " Point(" . $this->input('long') . ", " . $this->input('lat') . "))"
                    . " < 8000";

                $query->whereRaw($subqueryDistanceWhere);
            });
        }
    }

    public function search($search)
    {
        return CoreHelper::searchTranslated($this, 'title', App::getLocale(), $search);
    }

    public function title($title)
    {
        return CoreHelper::searchTranslated($this, 'title', App::getLocale(), $title);
    }

    public function category($id)
    {
        return $this->whereHas('categories', function ($query) use ($id) {
            return $query->where('id', $id);
        });
    }

    public function vendor($id)
    {
        return $this->whereHas('vendorProducts', function ($query) use ($id) {
            return $query->where('vendor_id', $id);
        });
    }

    public function vendorType($types)
    {
        return $this->whereHas('vendorProducts.vendor', function ($query) use ($types) {
            return $query->whereJsonContains('meta->type', $types);
        });
    }

    public function minPrice($min_price)
    {
        return $this->where('price', '>=', $min_price);
    }

    public function maxPrice($max_price)
    {
        return $this->where('price', '<=', $max_price);
    }

    public function feature($feature)
    {
        return $this->inRandomOrder();
    }

    public function latest($latest)
    {
        return $this->orderBy('created_at', 'desc');
    }

    public function bestSeller($bestSeller)
    {
        return $this->orderBy('sells_count', 'desc');
    }

    public function sort($sort)
    {
        if ($sort == 'price_asc') {
            return $this->orderBy('price', 'asc');
        }

        if ($sort == 'price_desc') {
            return $this->orderBy('price', 'desc');
        }
    }
}
