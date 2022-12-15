<?php

namespace Vtlabs\Ecommerce\Models;

use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    /**
     * {@inheritdoc}
     */
    protected $table = 'ecommerce_addresses';

    /**
     * {@inheritdoc}
     */
    protected $guarded = [];

    /**
     * {@inheritdoc}
     */
    protected $casts = [
        'longitude' => 'float',
        'latitude' => 'float'
    ];

    public function user()
    {
        return $this->hasOne(config('vtlabs_ecommerce.models.user'));
    }
}
