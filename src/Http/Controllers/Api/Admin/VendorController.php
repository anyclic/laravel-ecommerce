<?php

namespace Vtlabs\Ecommerce\Http\Controllers\Api\Admin;

use Illuminate\Http\Request;
use Vtlabs\Ecommerce\Models\Vendor;
use Illuminate\Support\Facades\Gate;
use Vtlabs\Core\Services\UserService;
use Illuminate\Database\Eloquent\Builder;
use Vtlabs\Ecommerce\Filters\VendorFilter;
use Vtlabs\Core\Http\Controllers\Controller;
use Vtlabs\Ecommerce\Http\Resources\Admin\VendorAdminResource;

class VendorController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'pagination' => 'sometimes|boolean',
            'name' => 'sometimes|string',
            'user' => 'sometimes|string',
            'category' => 'sometimes|exists:categories,id'
        ]);

        $vendors = Vendor::filter($request->all(), VendorFilter::class);

        if ($request->pagination == '0') {
            $vendors = $vendors->get();
        } else {
            $vendors = $vendors->paginate();
        }

        return VendorAdminResource::collection($vendors);
    }

    public function show($id)
    {
        return new VendorAdminResource(Vendor::findOrFail($id));
    }

    public function store(Request $request)
    {
        $request->validate([
            'email' => 'required|email|max:255|unique:users',
            'mobile_number' => 'required|unique:users',
            'password' => 'required|min:6',
            'name_translations' => 'required|translation',
            'tagline_translations' => 'sometimes|translation|nullable',
            'details_translations' => 'sometimes|translation|nullable',
            'meta' => 'sometimes|json|nullable',
            'minimum_order' => 'sometimes|numeric',
            'delivery_fee' => 'sometimes|numeric',
            'area' => 'sometimes|string',
            'address' => 'sometimes|string',
            'longitude' => 'sometimes|numeric|min:-180|max:180',
            'latitude' => 'sometimes|numeric|min:-90|max:90',
            'categories' => 'sometimes|array|exists:categories,id',
            'image' => 'sometimes|image',
            'is_verified' => 'sometimes|boolean',
        ]);

        request()->merge([
            "name" => request()->input('_name'),
            "tagline" => request()->input('_tagline') ?? null,
            "details" => request()->input('_details') ?? null
        ]);

        if ($request->meta) {
            request()->merge([
                "meta" => json_decode($request->meta)
            ]);
        }

        // create user for vendor first  
        $userName = $request->name[array_keys(($request->name))[0]];
        $userData = collect($request->only(['email', 'mobile_number', 'password']));
        $userData = $userData->merge(['name' => $userName, 'mobile_verified' => 1, 'roles' => ['vendor']]);
        $user = UserService::create($userData);


        $vendor = Vendor::where('user_id', $user->id)->firstOrFail();
        $vendor = $vendor->fill($request->only([
            'name', 'tagline', 'details', 'meta', 'minimum_order', 'delivery_fee', 'area',
            'address', 'longitude', 'latitude', 'is_verified'
        ]));

        $vendor->categories()->sync($request->categories);

        $vendor->save();

        if ($request->image) {
            $vendor->clearMediaCollection('images');
            $vendor->addMedia($request->image)->toMediaCollection("images");
        }

        return new VendorAdminResource($vendor->fresh());
    }


    public function update(Request $request, $id)
    {
        $vendor = Vendor::findOrFail($id);

        $request->validate([
            'name_translations' => 'required|translation',
            'tagline_translations' => 'sometimes|translation|nullable',
            'details_translations' => 'sometimes|translation|nullable',
            'meta' => 'sometimes|json|nullable',
            'minimum_order' => 'sometimes|numeric',
            'delivery_fee' => 'sometimes|numeric',
            'area' => 'sometimes|string',
            'address' => 'sometimes|string',
            'longitude' => 'sometimes|numeric|min:-180|max:180',
            'latitude' => 'sometimes|numeric|min:-90|max:90',
            'categories' => 'sometimes|array|exists:categories,id',
            'image' => 'sometimes|image',
            'is_verified' => 'sometimes|boolean',
        ]);

        request()->merge([
            "name" => request()->input('_name'),
            "tagline" => request()->input('_tagline') ?? null,
            "details" => request()->input('_details') ?? null
        ]);

        if ($request->meta) {
            request()->merge([
                "meta" => json_decode($request->meta)
            ]);
        }

        $vendor = $vendor->fill($request->only([
            'name', 'tagline', 'details', 'meta', 'minimum_order', 'delivery_fee', 'area',
            'address', 'longitude', 'latitude',  'is_verified'
        ]));

        $vendor->categories()->sync($request->categories);

        $vendor->save();

        if ($request->image) {
            $vendor->clearMediaCollection('images');
            $vendor->addMedia($request->image)->toMediaCollection("images");
        }

        return new VendorAdminResource($vendor->fresh());
    }

    public function destroy($id)
    {
        Gate::authorize('delete');

        $vendor = Vendor::findOrFail($id);
        $vendor->user->removeRole('vendor');
        $vendor->delete();

        return response()->json([], 200);
    }
}
