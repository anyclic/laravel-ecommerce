<?php

namespace Vtlabs\Ecommerce\Http\Controllers\Api\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Vtlabs\Core\Services\UserService;
use Illuminate\Database\Eloquent\Builder;
use Vtlabs\Core\Http\Controllers\Controller;
use Vtlabs\Ecommerce\Models\DeliveryProfile;
use Vtlabs\Ecommerce\Filters\DeliveryProfileFilter;
use Vtlabs\Ecommerce\Http\Resources\Admin\DeliveryProfileAdminResource;

class DeliveryProfileController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'user' => 'sometimes'
        ]);

        $deliveryProfiles = DeliveryProfile::filter($request->all(), DeliveryProfileFilter::class);

        return DeliveryProfileAdminResource::collection($deliveryProfiles->paginate());
    }

    public function show($id)
    {
        return new DeliveryProfileAdminResource(DeliveryProfile::findOrFail($id));
    }

    public function store(Request $request)
    {
        $request->validate([
            'email' => 'required|email|max:255|unique:users',
            'mobile_number' => 'required|unique:users',
            'password' => 'required|min:6',
            'is_online' => 'required|boolean',
            'is_verified' => 'sometimes|boolean',
            'assigned' => 'required|boolean',
            'longitude' => 'required|numeric|min:-180|max:180',
            'latitude' => 'required|numeric|min:-90|max:90',
            'meta' => 'sometimes|json|nullable'
        ]);

        // create user for delivery first  
        $userData = collect($request->only(['email', 'mobile_number', 'password']));
        $userData = $userData->merge(['name' => $userData['mobile_number'], 'mobile_verified' => 1, 'roles' => ['delivery']]);
        $user = UserService::create($userData);

        $deliveryProfile = DeliveryProfile::where('user_id', $user->id)->firstOrFail();

        if ($request->meta) {
            request()->merge([
                "meta" => ["owned_by" => Auth::id()]
            ]);
        }

        $deliveryProfile->fill($request->only([
            'is_online', 'is_verified', 'longitude', 'latitude', 'assigned', 'meta'
        ]));

        $deliveryProfile->save();

        return new DeliveryProfileAdminResource($deliveryProfile->fresh());
    }

    public function update(Request $request, $id)
    {
        $deliveryProfile = DeliveryProfile::findOrFail($id);

        $request->validate([
            'is_online' => 'required|boolean',
            'assigned' => 'required|boolean',
            'is_verified' => 'sometimes|boolean',
            'longitude' => 'required|numeric|min:-180|max:180',
            'latitude' => 'required|numeric|min:-90|max:90',
            'meta' => 'sometimes|json|nullable'
        ]);

        if ($request->meta) {
            request()->merge([
                "meta" => json_decode($request->meta)
            ]);
        }

        $deliveryProfile->fill($request->only([
            'is_online', 'is_verified', 'longitude', 'latitude', 'assigned', 'meta'
        ]));

        $deliveryProfile->save();

        return new DeliveryProfileAdminResource($deliveryProfile->fresh());
    }

    public function destroy($id)
    {
        Gate::authorize('delete');

        DeliveryProfile::findOrFail($id)->delete();

        return response()->json([], 200);
    }
}
