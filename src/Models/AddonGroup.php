<?php

namespace Vtlabs\Ecommerce\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class AddonGroup extends Model
{
    use HasTranslations;
    
    protected $table = 'ecommerce_product_addon_groups';

    protected $guarded = [];

    protected $casts = [
        'title' => 'json',
        'max_choices' => 'integer',
        'min_choices' => 'integer'
    ];

    public $translatable = ['title'];

    public function addonChoices()
    {
        return $this->hasMany(config('vtlabs_ecommerce.models.addon_choice'), 'product_addon_group_id', 'id');
    }
}
