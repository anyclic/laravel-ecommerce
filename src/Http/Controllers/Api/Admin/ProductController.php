<?php

namespace Vtlabs\Ecommerce\Http\Controllers\Api\Admin;

use ZipArchive;
use League\Csv\Reader;
use Illuminate\Http\Request;
use Vtlabs\Core\Helpers\CoreHelper;
use Vtlabs\Ecommerce\Models\Vendor;
use Illuminate\Support\Facades\Gate;
use Vtlabs\Ecommerce\Models\Product;
use Vtlabs\Ecommerce\Models\VendorProduct;
use Vtlabs\Ecommerce\Filters\ProductFilter;
use Vtlabs\Core\Http\Controllers\Controller;
use Vtlabs\Ecommerce\Http\Resources\Admin\ProductAdminResource;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'title' => 'sometimes|string',
            'product' => 'sometimes|exists:categories,id'
        ]);

        $products = Product::filter($request->all(), ProductFilter::class);

        return ProductAdminResource::collection($products->paginate());
    }

    public function show($id)
    {
        return new ProductAdminResource(Product::findOrFail($id));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title_translations' => 'required|translation',
            'detail_translations' => 'required|translation',
            'meta' => 'sometimes|json|nullable',
            'price' => 'required|numeric',
            'sale_price' => 'sometimes|nullable|numeric',
            'sale_price_from' => 'sometimes|date|nullable',
            'sale_price_to' => 'sometimes|date|nullable',
            'stock_quantity' => 'sometimes|numeric',
            'stock_low_threshold' => 'sometimes|numeric',
            'vendor_id' => 'required|exists:ecommerce_vendors,id',
            'categories' => 'sometimes|array|exists:categories,id',
            'addon_groups' => 'sometimes|array',
            'addon_groups.*.title_translations' => 'required|translation',
            'addon_groups.*.min_choices' => 'required|integer',
            'addon_groups.*.max_choices' => 'required|integer',
            'addon_groups.*.choices' => 'array|required',
            'addon_groups.*.choices.*.title_translations' => 'required|translation',
            'addon_groups.*.choices.*.price' => 'required',
            'images.*' => 'sometimes|image',
            'type' => 'required|integer',
            'gstype' => 'required|integer',
        ]);

        request()->merge([
            "title" => request()->input('_title'),
            "detail" => request()->input('_detail') ?? null
        ]);

        if ($request->meta) {
            request()->merge([
                "meta" => json_decode($request->meta)
            ]);
        }

        $product = Product::create($request->only([
            'title', 'detail', 'meta', 'price', 'type', 'gstype'
        ]));

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
        if (!empty($request->images)) {
            foreach ($request->images as $image) {
                $product->addMedia($image)->toMediaCollection("images");
            }
        }

        // addons
        if ($request->has('addon_groups')) {
            $product->attachAddons($request->all());
        }

        return new ProductAdminResource($product->fresh());
    }

    public function update(Product $product, Request $request)
    {
        $request->validate([
            'title_translations' => 'required|translation',
            'detail_translations' => 'required|translation',
            'meta' => 'sometimes|json|nullable',
            'price' => 'required|numeric',
            'sale_price' => 'sometimes|nullable|numeric',
            'sale_price_from' => 'sometimes|date|nullable',
            'sale_price_to' => 'sometimes|date|nullable',
            'stock_quantity' => 'sometimes|numeric',
            'stock_low_threshold' => 'sometimes|numeric',
            'vendor_id' => 'required|exists:ecommerce_vendors,id',
            'categories' => 'sometimes|array|exists:categories,id',
            'addon_groups' => 'sometimes|array',
            'addon_groups.*.title_translations' => 'required|translation',
            'addon_groups.*.min_choices' => 'required|integer',
            'addon_groups.*.max_choices' => 'required|integer',
            'addon_groups.*.choices' => 'array|required',
            'addon_groups.*.choices.*.title_translations' => 'required|translation',
            'addon_groups.*.choices.*.price' => 'required',
            'images.*' => 'sometimes|image',
            'type' => 'required|integer',
            'gstype' => 'required|integer',
        ]);

        request()->merge([
            "title" => request()->input('_title'),
            "detail" => request()->input('_detail') ?? null
        ]);

        if ($request->meta) {
            request()->merge([
                "meta" => json_decode($request->meta)
            ]);
        }

        $product->fill($request->only([
            'title', 'detail', 'meta', 'type', 'gstype'
        ]));

        $product->categories()->sync($request->categories);

        $product->save();

        // attach product categories with vendor
        $vendor = Vendor::find($request->vendor_id);
        $vendorProductCategories = [];
        for ($i = 0; $i < count($request->categories); $i++) {
            $vendorProductCategories[$request->categories[$i]] = ['product_id' => $product->id];
        }
        $vendor->productCategories()->wherePivot('product_id', $product->id)->sync($vendorProductCategories);

        // update vendor specific details of product
        $vendorProduct = VendorProduct::where('product_id', $product->id)->where('vendor_id', $request->vendor_id)->first();
        if ($vendorProduct) {
            $vendorProduct->fill($request->only([
                'price', 'sale_price', 'sale_price_from', 'sale_price_to',
                'stock_quantity', 'stock_low_threshold'
            ]));
            $vendorProduct->save();
        } else {
            $vendorProductAttributes = array_merge($request->only([
                'vendor_id', 'price', 'sale_price', 'sale_price_from', 'sale_price_to',
                'stock_quantity', 'stock_low_threshold'
            ]), ["product_id" => $product->id]);
            VendorProduct::create($vendorProductAttributes);
        }

        // images
        if (!empty($request->images)) {
            $product->clearMediaCollection('images');
            foreach ($request->images as $image) {
                $product->addMedia($image)->toMediaCollection("images");
            }
        }

        // addons
        if ($request->has('addon_groups')) {
            $product->attachAddons($request->all());
        }

        return new ProductAdminResource($product->fresh());
    }

    public function destroy($id)
    {
        Gate::authorize('delete');

        Product::findOrFail($id)->delete();

        return response()->json([], 200);
    }

    public function downloadTemplateFile(Request $request)
    {
        return response()->download(public_path('assets/vendor/product/data-template/product-data.zip'));
    }

    public function importFromFile(Request $request)
    {
        $request->validate([
            'csv' => 'required|file'
        ]);

        $zip = new ZipArchive;

        $res = $zip->open($request->file('csv')->getRealPath());

        if ($res === TRUE) {
            $zip->extractTo(storage_path('app/public'));
            $zip->close();
        }

        if (file_exists(storage_path('app/public/product-data/products.csv'))) {
            $csv = Reader::createFromPath(storage_path('app/public/product-data/products.csv'), 'r');

            $csv->setHeaderOffset(0);
            $csv->setDelimiter(',');
            $records = $csv->getRecords(); //returns all the CSV records as an Iterator object

            foreach ($records as $record) {
                $record['categories'] = array_map('trim', explode(',', $record['categories']));

                // validate record
                $validator = Validator::make($record, [
                    'title' => 'required|string',
                    'detail' => 'required|string',
                    'price' => 'required|numeric',
                    'stock_quantity' => 'required|numeric',
                    'meta_prescription' => 'required|numeric',
                    'vendor_id' => 'required|exists:ecommerce_vendors,id',
                    'categories' => 'required|array|exists:categories,id',
                    'image_name' => 'sometimes'
                ]);
                $validator->validate();

                $product = new Product();
                $product->price = $record['price'];
                $product->meta = ["prescription" => $record['meta_prescription']];

                CoreHelper::fillTranslation($product, ["title" => $record['title'], "detail" => $record['detail']]);

                $product->save();

                // categories
                $product->categories()->sync($record['categories']);

                // attach product categories with vendor
                $vendor = Vendor::find($record['vendor_id']);

                $vendorProductCategories = [];
                for ($i = 0; $i < count($record['categories']); $i++) {
                    $vendorProductCategories[$record['categories'][$i]] = ['product_id' => $product->id];
                }
                $vendor->productCategories()->wherePivot('product_id', $product->id)->sync($vendorProductCategories);

                // create vendor specific details of product
                VendorProduct::create([
                    'vendor_id' => $record['vendor_id'],
                    'price' => $record['price'],
                    'stock_quantity' => $record['stock_quantity'],
                    "product_id" => $product->id
                ]);

                if (!empty($record['image_name'])) {
                    $product->addMedia(storage_path('app/public/product-data/image/' . $record['image_name']))->preservingOriginal()->toMediaCollection("images");
                }
            }

            // cleaning
            Storage::deleteDirectory('product-data');

            return response()->json([], 200);
        }

        return response()->json([], 400);
    }
}
