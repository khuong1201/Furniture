<?php

namespace Modules\Product\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Modules\Shared\Http\Controllers\BaseController;
use Modules\Shared\Http\Resources\ApiResponse;
use Modules\Product\Services\ProductService;
use Modules\Product\Domain\Models\Product;
use Modules\Product\Http\Requests\StoreProductRequest;
use Modules\Product\Http\Requests\UpdateProductRequest;
use OpenApi\Attributes as OA;

#[OA\Tag(
    name: "Products",
    description: "API quản lý sản phẩm (Public xem, Admin quản lý)"
)]

class ProductController extends BaseController
{
    public function __construct(ProductService $service)
    {
        parent::__construct($service);
    }

    #[OA\Get(
        path: "/public/products",
        summary: "Lấy danh sách sản phẩm (Public - Chỉ hiện Active)",
        tags: ["Products"],
        parameters: [
            new OA\Parameter(name: "page", in: "query", schema: new OA\Schema(type: "integer")),
            new OA\Parameter(name: "per_page", in: "query", schema: new OA\Schema(type: "integer")),
            new OA\Parameter(name: "category_uuid", in: "query", schema: new OA\Schema(type: "string")),
            new OA\Parameter(name: "search", in: "query", schema: new OA\Schema(type: "string")),
        ],
        responses: [ new OA\Response(response: 200, description: "Success") ]
    )]
    public function index(Request $request): JsonResponse
    {
        $filters = $request->all();
        $filters['is_active'] = true; 

        $data = $this->service->paginate($request->get('per_page', 15), $filters);
        return response()->json(ApiResponse::paginated($data));
    }

    #[OA\Get(
        path: "/public/products/{uuid}",
        summary: "Xem chi tiết sản phẩm (Public)",
        tags: ["Products"],
        parameters: [
            new OA\Parameter(name: "uuid", in: "path", required: true, schema: new OA\Schema(type: "string", format: "uuid"))
        ],
        responses: [ 
            new OA\Response(response: 200, description: "Success"),
            new OA\Response(response: 404, description: "Not found")
        ]
    )]
    public function show(string $uuid): JsonResponse
    {
        $product = $this->service->findByUuidOrFail($uuid);
        
        $product->load(['images', 'warehouses', 'category', 'promotions'])
                ->loadAvg('reviews', 'rating');

        return response()->json(ApiResponse::success($product));
    }

    #[OA\Get(
        path: "/admin/products",
        summary: "Danh sách sản phẩm (Admin - Xem cả ẩn)",
        security: [['bearerAuth' => []]],
        tags: ["Products"],
        responses: [ new OA\Response(response: 200, description: "Success") ]
    )]
    public function adminIndex(Request $request): JsonResponse
    {
        if (!$request->user()->hasPermissionTo('product.view')) {
             return response()->json(ApiResponse::error('Forbidden', 403), 403);
        }

        $data = $this->service->paginate($request->get('per_page', 15), $request->all());
        return response()->json(ApiResponse::paginated($data));
    }

    #[OA\Post(
        path: "/admin/products",
        summary: "Tạo sản phẩm mới (Admin)",
        security: [['bearerAuth' => []]],
        tags: ["Products"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["name", "sku", "price", "category_uuid"],
                properties: [
                    new OA\Property(property: "name", type: "string"),
                    new OA\Property(property: "category_uuid", type: "string", format: "uuid"),
                    new OA\Property(property: "price", type: "number"),
                    new OA\Property(property: "sku", type: "string"),
                ]
            )
        ),
        responses: [ new OA\Response(response: 201, description: "Created") ]
    )]
    public function store(StoreProductRequest $request): JsonResponse
    {
        $this->authorize('create', Product::class);

        $product = $this->service->create($request->validated());
        return response()->json(ApiResponse::success($product, 'Product created successfully', 201), 201);
    }

    #[OA\Put(
        path: "/admin/products/{uuid}",
        summary: "Cập nhật sản phẩm (Admin)",
        security: [['bearerAuth' => []]],
        tags: ["Products"],
        parameters: [
            new OA\Parameter(name: "uuid", in: "path", required: true, schema: new OA\Schema(type: "string", format: "uuid"))
        ],
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "name", type: "string"),
                    new OA\Property(property: "is_active", type: "boolean"),
                ]
            )
        ),
        responses: [ new OA\Response(response: 200, description: "Updated") ]
    )]
    public function update(UpdateProductRequest $request, string $uuid): JsonResponse
    {
        $product = $this->service->findByUuidOrFail($uuid);
        
        $this->authorize('update', $product);

        $product = $this->service->update($uuid, $request->validated());
        return response()->json(ApiResponse::success($product, 'Product updated successfully'));
    }

    #[OA\Delete(
        path: "/admin/products/{uuid}",
        summary: "Xóa sản phẩm (Admin)",
        security: [['bearerAuth' => []]],
        tags: ["Products"],
        parameters: [
            new OA\Parameter(name: "uuid", in: "path", required: true, schema: new OA\Schema(type: "string", format: "uuid"))
        ],
        responses: [ new OA\Response(response: 200, description: "Deleted") ]
    )]
    public function destroy(string $uuid): JsonResponse
    {
        $product = $this->service->findByUuidOrFail($uuid);
        
        $this->authorize('delete', $product);

        $this->service->delete($uuid);
        return response()->json(ApiResponse::success(null, 'Product deleted successfully'));
    }
}