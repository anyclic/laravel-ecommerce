<?php

namespace Vtlabs\Ecommerce\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Vtlabs\Ecommerce\Models\Address;
use Vtlabs\Core\Http\Controllers\Controller;
use Vtlabs\Ecommerce\Http\Resources\AddressResource;

class AddressController extends Controller
{
    public function index(Request $request)
    {
        $addresses = Address::where('user_id', Auth::user()->id)->get();
        return AddressResource::collection($addresses);
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required',
            'formatted_address' => 'required',
            'address1' => 'sometimes',
            'address2' => 'sometimes',
            'formatted_address' => 'required',
            'longitude' => 'required|numeric',
            'latitude' => 'required|numeric'
        ]);

        $address = Address::create([
            'title' => $request->title,
            'formatted_address' => $request->formatted_address,
            'address1' => $request->address1,
            'address2' => $request->address2,
            'longitude' => $request->longitude,
            'latitude' => $request->latitude,
            'user_id' => Auth::user()->id
        ]);

        return new AddressResource($address);
    }

    public function update(Address $address, Request $request)
    {
        $request->validate([
            'title' => 'required',
            'formatted_address' => 'required',
            'address1' => 'sometimes',
            'address2' => 'sometimes',
            'longitude' => 'required|numeric',
            'latitude' => 'required|numeric'
        ]);

        $address->fill($request->only(['title', 'formatted_address', 'longitude', 'latitude', 'address1', 'address2']));

        $address->save();

        return new AddressResource($address);
    }

    public function destroy(Address $address, Request $request)
    {
        $address->delete();
        return response()->json((object)[]);
    }
}