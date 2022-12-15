<?php

namespace Vtlabs\Ecommerce\Http\Controllers\Api;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Vtlabs\Core\Models\User\User;
use Illuminate\Support\Facades\DB;
use Vtlabs\Ecommerce\Models\Order;
use Vtlabs\Core\Helpers\CoreHelper;
use Vtlabs\Ecommerce\Models\Vendor;
use Illuminate\Support\Facades\Auth;
use Vtlabs\Ecommerce\Filters\OrderFilter;
use Vtlabs\Ecommerce\Filters\VendorFilter;
use Vtlabs\Ecommerce\Services\OrderService;
use Vtlabs\Core\Http\Controllers\Controller;
use Vtlabs\Ecommerce\Http\Resources\VendorResource;
use Vtlabs\Ecommerce\Http\Resources\VendorRatingResource;

class VendorController extends Controller
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
            'lat' => 'sometimes|numeric',
            'long' => 'sometimes|numeric',
            'search' => 'sometimes'
        ]);

        $vendors = Vendor::filter($request->all(), VendorFilter::class);

        $vendors = $vendors->with(['categories']);

        return VendorResource::collection($vendors->paginate());
    }

    public function show(Vendor $vendor, Request $request)
    {
        return new VendorResource($vendor);
    }

    public function showMyProfile(Request $request)
    {
        $vendor = Vendor::findByUser(Auth::user()->id);
        return new VendorResource($vendor);
    }

    public function update(Vendor $vendor, Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'tagline' => 'sometimes|string',
            'details' => 'sometimes|string',
            'meta' => 'sometimes|json',
            'minimum_order' => 'sometimes|numeric',
            'delivery_fee' => 'sometimes|numeric',
            'area' => 'sometimes|string',
            'address' => 'sometimes|string',
            'longitude' => 'sometimes|numeric|min:-180|max:180',
            'latitude' => 'sometimes|numeric|min:-90|max:90',
            'categories' => 'sometimes|array|exists:categories,id',
            'image_urls' => 'sometimes|array',
        ]);

        if ($request->meta) {
            request()->merge([
                "meta" => json_decode($request->meta)
            ]);
        }

        $vendor->fill($request->only([
            'meta', 'minimum_order', 'delivery_fee', 'area', 'address', 'longitude', 'latitude'
        ]));

        CoreHelper::fillTranslation($vendor, $request->only(['name', 'tagline', 'details']));

        $vendor->categories()->sync($request->categories);

        $vendor->save();

        // images
        if (!empty($request->image_urls)) {
            $newMediaItems = [];
            foreach ($request->image_urls as $imageUrl) {
                $newMediaItems[] = $vendor->addMediaFromUrl($imageUrl)->toMediaCollection("images");
            }
            $vendor->clearMediaCollectionExcept('images', $newMediaItems);
        }

        return new VendorResource($vendor->fresh());
    }

    public function insights(Vendor $vendor, Request $request)
    {
        $request->validate([
            'duration' => 'required|in:hours,days,months,years',
            'limit' => 'required|numeric'
        ]);

        $orders  = Order::whereHas('vendor', function ($query) use ($vendor) {
            $query->where('id', $vendor->id);
        });

        $orders = OrderService::summarize($orders, $request->duration, $request->limit);

        $summary = [
            'orders' => Order::filter(['vendor_id' => $vendor->id, 'duration' => $request->duration], OrderFilter::class)->count(),
            'revenue' => Order::filter(['vendor_id' => $vendor->id, 'duration' => $request->duration], OrderFilter::class)->sum('total'),
            'pending' => Order::filter(['vendor_id' => $vendor->id, 'duration' => $request->duration, 'active' => true], OrderFilter::class)->count(),
            'chart_data' => $orders->get()
        ];

        return response()->json($summary);
    }

    public function ratingList(Vendor $vendor, Request $request)
    {
        return VendorRatingResource::collection($vendor->raters(User::class)->orderByDesc('pivot_created_at')->paginate());
    }

    public function ratingStore(Vendor $vendor, Request $request)
    {
        $request->validate([
            'rating' => 'required|numeric',
            'review' => 'required'
        ]);

        $user = Auth::user();

        // $user->unrate($vendor);
        $user->rate($vendor, $request->rating, $request->review);

        // update average rating of vendor for stats
        $vendor->average_ratings +=  $vendor->averageRating(User::class);
        $vendor->save();

        return response()->json([], 200);
    }

    public function ratingSummary(Vendor $vendor)
    {
        return response()->json([
            "average_rating" => $vendor->averageRating(User::class),
            "total_ratings" => $vendor->raters(User::class)->count(),
            "summary" => DB::table('ratings')->selectRaw('count(*) as total, ROUND(rating) as rounded_rating')
                ->where('rateable_type', Vendor::class)
                ->where('rateable_id', $vendor->id)
                ->where('rater_type', User::class)
                ->groupBy('rounded_rating')
                ->get()
        ]);
    }

    public function favourites(Request $request)
    {
        return VendorResource::collection(Auth::user()->favorite(Vendor::class)->flatten());
    }

    public function toggleFavourite(Vendor $vendor)
    {
        $vendor->toggleFavorite();

        return response()->json([]);
    }
}
