<?php

namespace Vtlabs\Ecommerce\Services;

use Carbon\Carbon;
use Vtlabs\Core\Models\User\User;
use Vtlabs\Ecommerce\Models\Order;
use Vtlabs\Core\Helpers\CoreHelper;
use Vtlabs\Ecommerce\Models\Coupon;
use Vtlabs\Ecommerce\Models\Vendor;
use Vtlabs\Ecommerce\Models\Address;
use Vtlabs\Ecommerce\Models\Product;
use Vtlabs\Ecommerce\Events\NewOrder;
use Vtlabs\Ecommerce\Models\AddonChoice;
use Vtlabs\Payment\Models\PaymentMethod;
use Illuminate\Support\Facades\Validator;
use Vtlabs\Ecommerce\Models\OrderAddress;
use Vtlabs\Ecommerce\Models\OrderProduct;
use Vtlabs\Ecommerce\Models\VendorProduct;
use Vtlabs\Ecommerce\Models\OrderProductAddon;

class CheckoutService
{
    private $user;
    private $data;
    private $settings;

    public function __construct(array $data, User $user)
    {
        $this->data = $data;
        $this->user = $user;
        $this->settings = CoreHelper::settingsAsDictionary();
    }
    public function validator()
    {
        $validator = Validator::make($this->data, [
            'meta' => 'sometimes|json',
            'coupon_code' => 'sometimes',
            'notes' => 'string|nullable',
            'address_id' => 'required|exists:ecommerce_addresses,id',
            'payment_method_slug' => 'required|exists:payment_methods,slug',
            'products' => 'required|array',
            'products.*.id' => 'required|exists:ecommerce_vendor_products,id',
            'products.*.quantity' => 'required|integer|min:1',
            'products.*.addons' => 'array',
            'products.*.addons.*.choice_id' => 'required|exists:ecommerce_product_addon_choices,id',
            'type' => 'in:ASAP,LATER',
            'scheduled_on' => 'required_if:type,LATER|date_format:Y-m-d H:i:s'
        ]);

        return $validator;
    }

    public function createOrder(): array
    {
        $orders = [];
        $address = Address::find($this->data['address_id']);

        // for each diffrent vendor create a new order
        $vendors = $this->groupProductsByVendor();

        foreach ($vendors as $vendorId => $vendorOrderItems) {
            $vendor = Vendor::find($vendorId);

            // increment vendors order count for stats
            $vendor->orders_count += 1;
            $vendor->save();

            $subtotal = 0;
            $subtotal = $vendorOrderItems['subtotal'];
            $deliveryFee = $this->calculateDeliveryFee($vendor);
            $taxes = $this->calculateTax($vendor, $subtotal);
            // @TODO: handle coupon in case of multiple vendor
            $discount = $this->calculateDiscount($vendor, $subtotal);
            $total = ($subtotal + $taxes + $deliveryFee) - $discount;

            // create an order instance
            $order = Order::create([
                'notes' => $this->data['notes'] ?? null,
                'subtotal' => $subtotal,
                'taxes' => $taxes,
                'delivery_fee' => $deliveryFee,
                'discount' => $discount,
                'total' => $total,
                'vendor_id' => $vendor->id,
                'user_id' => $this->user->id,
                'meta' => !empty($this->data['meta']) ? json_decode($this->data['meta']) : null
            ]);

            $this->saveOrderAddress($order);
            $this->saveOrderProducts($order, $vendorOrderItems['products']);

            // payment
            $paymentMethodSlug = $this->data['payment_method_slug'];
            $paymentMethod = PaymentMethod::where('slug', $paymentMethodSlug)->first();
            $payment = $this->user->createPayment($order, $total, $paymentMethodSlug);

            if ($paymentMethod->type == 'postpaid') {
                event(new NewOrder($order->fresh()));
            }

            array_push($orders, $order->id);
        }

        return $orders;
    }

    private function groupProductsByVendor(): array
    {
        $vendors = [];
        $products = $this->data['products'];

        foreach ($products as $productItem) {
            $product = VendorProduct::find($productItem['id']);

            // check if vendor already in array, if not create an empty array for vendor
            $vendors[$product->vendor_id] = isset($vendors[$product->vendor_id])
                ? $vendors[$product->vendor_id]
                : ['subtotal' => 0, 'products' => []];

            // @TODO: handle sales price
            $productTotal = $productItem['quantity'] * $product->price;

            // handle menu item choices
            $orderProductAddons = [];
            if (array_key_exists('addons', $productItem)) {
                foreach ($productItem['addons'] as $addon) {
                    $addonChoice = AddonChoice::find($addon['choice_id']);
                    $productTotal += $addonChoice->price; // add the price of add-on(item choice) in product's total price

                    array_push($orderProductAddons, [
                        'product_addon_choice_id' => $addonChoice->id,
                        'total' => $addonChoice->price
                    ]);
                }
            }

            $vendors[$product->vendor_id]['subtotal'] = $vendors[$product->vendor_id]['subtotal'] + $productTotal;
            array_push($vendors[$product->vendor_id]['products'], [
                'vendor_product_id' => $productItem['id'],
                'quantity' => $productItem['quantity'],
                'total' => $productTotal,
                'addons' => $orderProductAddons
            ]);
        }

        return $vendors;
    }

    private function calculateDeliveryFee($vendor)
    {
        $deliveryFee = $this->settings['delivery_fee'] ?? 0;
        return $deliveryFee;

        // @TODO: handle multiple delivery fee preferences
        // switch ($settings['delivery_fee_set_by']) {
        //     case 'store':
        //         $deliveryFee = $store->delivery_fee ? $store->delivery_fee : 0;
        //         break;
        //     case 'distance':
        //         // calculate distance between store and user's location to calculate delivery charges
        //         $storeLat = $store->latitude;
        //         $storeLng = $store->longitude;
        //         $userLat = $address->latitude;
        //         $userLng = $address->longitude;

        //         $distances = DB::select('SELECT ST_Distance_Sphere(Point(:store_lng,:store_lat),Point(:user_lng,:user_lat)) as distance',
        //             ['store_lng' => $storeLng, 'store_lat' => $storeLat, 'user_lng' => $userLng, 'user_lat' => $userLat,]);
        //         $distanceInKms = ceil($distances[0]->distance / 1000);
        //         $deliveryChargePerKm = $settings['delivery_fee_per_km_charge'] ? $settings['delivery_fee_per_km_charge'] : 0;
        //         $deliveryFee = $distanceInKms * $deliveryChargePerKm;
        //         break;
        // }
    }

    private function calculateTax($vendor, $amount)
    {
        $tax = $this->settings['tax_in_percent'] ?? 0;
        return ($amount * $tax) / 100;
    }

    private function calculateDiscount($vendor, $amount)
    {
        $discount = 0;

        if (!isset($this->data['coupon_code'])) {
            return 0;
        }

        try {
            $coupon = Coupon::getCouponFromCode($this->data['coupon_code'], $this->user);
            if ($coupon !== null) {
                if ($coupon->type == 'fixed') {
                    $discount = $coupon->reward;
                }

                if ($coupon->type == 'percent') {
                    $discount = ($amount * $coupon->reward) / 100;
                }

                $coupon->users()->attach($this->user->id, [
                    'used_at' => Carbon::now(),
                ]);
            }
        } catch (\Exception $ex) {
            //
        }

        return $discount;
    }

    private function saveOrderProducts(Order $order, array $products)
    {
        // save order products
        foreach ($products as $productItem) {
            $orderProduct = OrderProduct::create([
                'total' => $productItem['total'],
                'quantity' => $productItem['quantity'],
                'vendor_product_id' => $productItem['vendor_product_id'],
                'order_id' => $order->id
            ]);

            // update product's sells count
            Product::updateSellsCount($productItem['vendor_product_id']);

            foreach ($productItem['addons'] as $addOn) {
                // save order product addons
                OrderProductAddon::create([
                    'total' => $addOn['total'],
                    'product_addon_choice_id' => $addOn['product_addon_choice_id'],
                    'order_product_id' => $orderProduct->id
                ]);
            }
        }
    }

    private function saveOrderAddress(Order $order)
    {
        $selectedAddress = Address::find($this->data['address_id']);
        OrderAddress::create([
            'order_id' => $order->id,
            'formatted_address' => $selectedAddress->formatted_address,
            'longitude' => $selectedAddress->longitude,
            'latitude' => $selectedAddress->latitude,
        ]);
    }
}
