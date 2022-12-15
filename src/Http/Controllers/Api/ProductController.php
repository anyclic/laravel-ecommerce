<?php

namespace Vtlabs\Ecommerce\Http\Controllers\Api;

use Illuminate\Http\Request;
use Vtlabs\Core\Models\User\User;
use Illuminate\Support\Facades\DB;
use Vtlabs\Core\Helpers\CoreHelper;
use Vtlabs\Ecommerce\Models\Vendor;
use Illuminate\Support\Facades\Auth;
use Vtlabs\Ecommerce\Models\Product;
use Rennokki\Rating\Models\RaterModel;
use Vtlabs\Ecommerce\Models\VendorProduct;
use Vtlabs\Ecommerce\Filters\ProductFilter;
use Vtlabs\Core\Http\Controllers\Controller;
use Vtlabs\Ecommerce\Http\Resources\ProductResource;
use Vtlabs\Ecommerce\Http\Resources\ProductRatingResource;
use Vtlabs\Ecommerce\Http\Resources\ProductRatingByVendorResource;

class ProductController extends Controller
{
    public function __construct()
    {
        if (array_key_exists('HTTP_AUTHORIZATION', $_SERVER)) {
            $this->middleware('auth:api');
        }
    }

    public function index(Request $request)
    {
        $request->validate([
            'category' => 'sometimes|exists:categories,id',
            'vendor' => 'sometimes|exists:ecommerce_vendors,id',
            'vendor_type' => 'sometimes|in:trader,farmer',
            'search' => 'sometimes',
            'lat' => 'sometimes|numeric',
            'long' => 'sometimes|numeric'
        ]);

        $products = Product::filter($request->all(), ProductFilter::class);

        return ProductResource::collection($products->paginate());
    }

    public function show(Product $product, Request $request)
    {
        return new ProductResource($product);
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string',
            'detail' => 'required|string',
            'meta' => 'sometimes|json',
            'price' => 'required|numeric',
            'stock_quantity' => 'required|numeric',
            'vendor_id' => 'required|exists:ecommerce_vendors,id',
            'categories' => 'sometimes|array|exists:categories,id',
            'addon_groups' => 'sometimes|array',
            'addon_groups.*.title' => 'required|string',
            'addon_groups.*.min_choices' => 'required|integer',
            'addon_groups.*.max_choices' => 'required|integer',
            'addon_groups.*.choices' => 'array|required',
            'addon_groups.*.choices.*.title' => 'required|string',
            'addon_groups.*.choices.*.price' => 'required',
            'image_urls' => 'sometimes|array',
            'type' => 'required|integer',
            'gstype' => 'required|integer',
        ]);

        $product = new Product();

        if ($request->meta) {
            request()->merge([
                "meta" => json_decode($request->meta)
            ]);
        }

        $product->fill($request->only('price', 'meta', 'type', 'gstype'));

        CoreHelper::fillTranslation($product, $request->only(['title', 'detail']));

        $product->save();

        $product->categories()->sync($request->categories);

        // attach product categories with vendor
        $vendor = Vendor::find($request->vendor_id);
        $vendorProductCategories = [];
        for ($i = 0; $i < count($request->categories); $i++) {
            $vendorProductCategories[$request->categories[$i]] = ['product_id' => $product->id];
        }
        $vendor->productCategories()->wherePivot('product_id', $product->id)->sync($vendorProductCategories);

        $vendorProductAttributes = array_merge($request->only([
            'vendor_id', 'price', 'sale_price', 'sale_price_from', 'sale_price_to',
            'stock_quantity', 'stock_low_threshold'
        ]), ["product_id" => $product->id]);

        // create vendor specific details of product
        VendorProduct::create($vendorProductAttributes);

        // images
        if (!empty($request->image_urls)) {
            foreach ($request->image_urls as $imageUrl) {
                $product->addMediaFromUrl($imageUrl)->toMediaCollection("images");
            }
        }

        // addons
        if ($request->has('addon_groups')) {
            $product->attachAddons($request->all(), false);
        }

        return new ProductResource($product->fresh());
    }

    public function update(Product $product, Request $request)
    {
        $request->validate([
            'title' => 'required|string',
            'detail' => 'required|string',
            'price' => 'required|numeric',
            'meta' => 'sometimes|json',
            'stock_quantity' => 'required|numeric',
            'vendor_id' => 'required|exists:ecommerce_vendors,id',
            'categories' => 'sometimes|array|exists:categories,id',
            'addon_groups' => 'sometimes|array',
            'addon_groups.*.title' => 'required|string',
            'addon_groups.*.min_choices' => 'required|integer',
            'addon_groups.*.max_choices' => 'required|integer',
            'addon_groups.*.choices' => 'array|required',
            'addon_groups.*.choices.*.title' => 'required|string',
            'addon_groups.*.choices.*.price' => 'required',
            'image_urls' => 'sometimes|array',
            'type' => 'required|integer',
            'gstype' => 'required|integer',
        ]);

        if ($request->meta) {
            request()->merge([
                "meta" => json_decode($request->meta)
            ]);
        }

        $product->fill($request->only('price', 'meta', 'type', 'gstype'));

        CoreHelper::fillTranslation($product, $request->only(['title', 'detail']));

        $product->save();

        $product->categories()->sync($request->categories);

        // attach product categories with vendor
        $vendor = Vendor::find($request->vendor_id);
        $vendorProductCategories = [];
        for ($i = 0; $i < count($request->categories); $i++) {
            $vendorProductCategories[$request->categories[$i]] = ['product_id' => $product->id];
        }
        $vendor->productCategories()->wherePivot('product_id', $product->id)->sync($vendorProductCategories);

        // update vendor specific details of product
        $vendorProduct = VendorProduct::where('product_id', $product->id)->where('vendor_id', $request->vendor_id)->first();
        $vendorProduct->fill($request->only([
            'price', 'sale_price', 'sale_price_from', 'sale_price_to',
            'stock_quantity', 'stock_low_threshold'
        ]));
        $vendorProduct->save();

        // images
        if (!empty($request->image_urls)) {
            $newMediaItems = [];
            foreach ($request->image_urls as $imageUrl) {
                $newMediaItems[] = $product->addMediaFromUrl($imageUrl)->toMediaCollection("images");
            }
            $product->clearMediaCollectionExcept('images', $newMediaItems);
        }

        // addons
        if ($request->has('addon_groups')) {
            $product->attachAddons($request->all(), false);
        }

        return new ProductResource($product->fresh());
    }

    public function ratingList(Product $product, Request $request)
    {
        return ProductRatingResource::collection($product->raters(User::class)->orderByDesc('pivot_created_at')->paginate());
    }

    public function ratingListByVendor(Vendor $vendor, Request $request)
    {
        $products = Product::filter(['vendor' => $vendor->id], ProductFilter::class)->get()->pluck(['id'])->toArray();
        return ProductRatingByVendorResource::collection(RaterModel::whereIn('rateable_id', $products)
            ->where(
                'rateable_type',
                Product::class
            )->orderByDesc('created_at')
            ->paginate());
    }

    public function ratingStore(Product $product, Request $request)
    {
        $request->validate([
            'rating' => 'required|numeric',
            'review' => 'required',
            'vendor_id' => 'required|exists: ecommerce_vendors,id'
        ]);

        $user = Auth::user();

        // $user->unrate($product);
        $user->rate($product, $request->rating, $request->review);

        // rate vendor as well so that we can use vendor's average rating to show top rated vendors
        $vendor = Vendor::find($request['vendor_id']);
        $user->rate($vendor, $request->rating, $request->review);

        // update average rating of vendor for stats
        $vendor->average_ratings +=  $vendor->averageRating(User::class);
        $vendor->save();

        return response()->json([], 200);
    }

    public function ratingSummary(Product $product)
    {
        return response()->json([
            "average_rating" => $product->averageRating(User::class),
            "total_ratings" => $product->raters(User::class)->count(),
            "summary" => DB::table('ratings')->selectRaw('count(*) as total, ROUND(rating) as rounded_rating')
                ->where('rateable_type', Product::class)
                ->where('rateable_id', $product->id)
                ->where('rater_type', User::class)
                ->groupBy('rounded_rating')
                ->get()
        ]);
    }

    public function favourites(Request $request)
    {
        return ProductResource::collection(Auth::user()->favorite(Product::class)->flatten());
    }

    public function toggleFavourite(Product $product)
    {
        $product->toggleFavorite();

        return response()->json([]);
    }
}
