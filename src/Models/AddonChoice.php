<?php

namespace Vtlabs\Ecommerce\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class AddonChoice extends Model
{
    use HasTranslations;

    protected $table = 'ecommerce_product_addon_choices';

    protected $guarded = [];

    protected $casts = [
        'title' => 'json',
        'price' => 'float'
    ];
    
    public $translatable = ['title'];
}
