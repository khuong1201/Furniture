<?php

namespace Modules\Product\Http\Controllers;

use Illuminate\Routing\Controller;
use Modules\Product\Http\Requests\StoreProductRequest;
use Modules\Product\Http\Requests\UpdateProductRequest;
use Modules\Product\Services\ProductService;

class ProductController extends Controller
{
    public function __construct(protected ProductService $service) {}

    public function index()
    {
        $filters = request()->only(['search', 'status', 'per_page']);
        $user = auth()->user();
        $detailed = in_array($user->role, ['admin', 'business']);

        $products = $this->service->list($filters);

        if ($detailed) {
            $products->load(['warehouses' => function($q) {
                $q->select('warehouses.id', 'name')->withPivot('quantity');
            }]);
        } else {
            foreach ($products as $product) {
                $product->total_quantity = $product->warehouses()->sum('warehouse_product.quantity');
            }
        }

        return response()->json($products);
    }

    public function store(StoreProductRequest $request)
    {
        $images = $request->file('images', []);
        if ($images && !is_array($images)) {
            $images = [$images];
        }
        $product = $this->service->create($request->validated(), $images);

        return response()->json($product, 201);
    } 

    public function show(string $uuid)
    {
        $user = auth()->user();
        $detailed = in_array($user->role, ['admin', 'business']);

        $product = $this->service->getProductWithStock($uuid, $detailed);

        return response()->json($product);
    }

    public function update(UpdateProductRequest $request, string $uuid)
    {
        $data = $request->validated();

        // Lấy file images
        $images = $request->file('images', []);
        if ($images && !is_array($images)) {
            $images = [$images];
        }

        // Debug log
        \Log::info('Update product request', ['data' => $data, 'files' => $images]);

        $product = $this->service->update($uuid, $data, $images);

        return response()->json($product, 200);
    }

    public function destroy(string $uuid)
    {
        $deleted = $this->service->delete($uuid); 
        return response()->json(['deleted' => $deleted]);
    }
}