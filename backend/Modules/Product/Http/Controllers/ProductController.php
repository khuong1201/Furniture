<?php

namespace Modules\Product\Http\Controllers;

use Illuminate\Http\Request;
use Modules\Shared\Http\Controllers\BaseController;
use Modules\Shared\Http\Resources\ApiResponse;
use Modules\Product\Services\ProductService;
use Modules\Product\Http\Requests\StoreProductRequest;
use Modules\Product\Http\Requests\UpdateProductRequest;
use Modules\Product\Http\Resources\ProductResource;

class ProductController extends BaseController
{
    public function __construct(ProductService $service)
    {
        parent::__construct($service);
    }

    public function index(Request $request): \Illuminate\Http\JsonResponse
    {
        $data = $this->service->paginate($request->get('per_page', 15), $request->all());
        
        return response()->json(ApiResponse::paginated($data));
    }

    public function store(Request $request): \Illuminate\Http\JsonResponse
    {
        $request = app(StoreProductRequest::class);
        $data = $request->validated();
        
        $product = $this->service->create($data);

        return response()->json(ApiResponse::success($product, 'Product created successfully', 201), 201);
    }

    public function show(string $uuid): \Illuminate\Http\JsonResponse
    {
        $product = $this->service->findByUuidOrFail($uuid);
        $product->load(['images', 'warehouses', 'category']);

        return response()->json(ApiResponse::success($product));
    }

    public function update(Request $request, string $uuid): \Illuminate\Http\JsonResponse
    {
        $request = app(UpdateProductRequest::class);
        $product = $this->service->update($uuid, $request->validated());
        return response()->json(ApiResponse::success($product, 'Product updated successfully'));
    }
    
}