<?php

namespace Vtlabs\Ecommerce\Models;

use Exception;
use Carbon\Carbon;
use EloquentFilter\Filterable;
use Vtlabs\Core\Models\User\User;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;
use Vtlabs\Ecommerce\Exceptions\CouponException;

class Coupon extends Model
{
    use Filterable,
        HasTranslations;
        
    protected $table = 'ecommerce_coupons';

    protected $guarded = [];

    protected $casts = [
        'title' => 'array',
        'detail' => 'array',
        'meta' => 'array',
        'reward' => 'integer'
    ];

    public $translatable = ['title', 'detail'];

    public function users()
    {
        return $this->belongsToMany(User::class, 'ecommerce_coupons_users');
    }

    /**
     * @throws Exception
     */
    public static function getCouponFromCode(string $couponCode, User $user): Coupon
    {
        try {
            $coupon = Coupon::where('code', $couponCode)->firstOrFail();
        } catch(\Exception $ex) {
            throw new Exception('Coupon not found');
        }

        $isUsed = $coupon->users()->wherePivot('user_id', $user->id)->exists();

        if($isUsed) {
            throw new \Exception('Coupon already used');
        }

        $isExpired = $coupon->expires_at ? Carbon::now()->gte($coupon->expires_at) : false;

        if($isExpired) {
            throw new \Exception('Coupon expired');
        }

        return $coupon;
    }
}
