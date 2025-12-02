<?php

namespace Modules\Product\Http\Controllers;

use Illuminate\Http\Request;
use Modules\Shared\Http\Controllers\BaseController;
use Modules\Shared\Http\Resources\ApiResponse;
use Modules\Product\Services\ProductService;
use Modules\Product\Domain\Models\Product;
use Modules\Product\Http\Requests\StoreProductRequest;
use Modules\Product\Http\Requests\UpdateProductRequest;
use Modules\Product\Http\Resources\ProductResource;
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
        path: "/api/public/products",
        summary: "Lấy danh sách sản phẩm (Public)",
        tags: ["Products"],
        parameters: [
            new OA\Parameter(name: "page", in: "query", description: "Page number", required: false, schema: new OA\Schema(type: "integer", default: 1)),
            new OA\Parameter(name: "per_page", in: "query", description: "Items per page", required: false, schema: new OA\Schema(type: "integer", default: 15)),
            new OA\Parameter(name: "category_uuid", in: "query", description: "Filter by Category UUID", required: false, schema: new OA\Schema(type: "string")),
            new OA\Parameter(name: "search", in: "query", description: "Search by name", required: false, schema: new OA\Schema(type: "string")),
        ],
        responses: [
            new OA\Response(response: 200, description: "Successful operation")
        ]
    )]

    public function index(Request $request): \Illuminate\Http\JsonResponse
    {
        $data = $this->service->paginate($request->get('per_page', 15), $request->all());
        
        return response()->json(ApiResponse::paginated($data));
    }

    #[OA\Post(
        path: "/api/admin/products",
        summary: "Tạo sản phẩm mới (Admin)",
        security: [['bearerAuth' => []]],
        tags: ["Products"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["name", "sku", "price", "category_uuid"],
                properties: [
                    new OA\Property(property: "category_uuid", type: "string", format: "uuid"),
                    new OA\Property(property: "name", type: "string", example: "iPhone 15 Pro Max"),
                    new OA\Property(property: "sku", type: "string", example: "IP15-PM-256"),
                    new OA\Property(property: "price", type: "number", format: "float", example: 1299.99),
                    new OA\Property(property: "description", type: "string", nullable: true),
                    new OA\Property(property: "is_active", type: "boolean", default: true),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: "Product created"),
            new OA\Response(response: 401, description: "Unauthenticated"),
            new OA\Response(response: 403, description: "Forbidden (Not Admin)")
        ]
    )]

    public function store(StoreProductRequest $request): \Illuminate\Http\JsonResponse
    {
        $data = $request->validated();
        
        $product = $this->service->create($data);

        return response()->json(ApiResponse::success($product, 'Product created successfully', 201), 201);
    }

    #[OA\Get(
        path: "/api/public/products/{uuid}",
        summary: "Xem chi tiết sản phẩm (Public)",
        tags: ["Products"],
        parameters: [
            new OA\Parameter(name: "uuid", in: "path", required: true, schema: new OA\Schema(type: "string", format: "uuid"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Successful operation"),
            new OA\Response(response: 404, description: "Product not found")
        ]
    )]

    public function show(string $uuid): \Illuminate\Http\JsonResponse
    {
        $product = $this->service->findByUuidOrFail($uuid);
        $product->load(['images', 'warehouses', 'category'])
                ->loadAvg('reviews', 'rating');

        return response()->json(ApiResponse::success($product));
    }

    #[OA\Put(
        path: "/api/admin/products/{uuid}",
        summary: "Cập nhật sản phẩm (Admin)",
        security: [['bearerAuth' => []]],
        tags: ["Products"],
        parameters: [
            new OA\Parameter(name: "uuid", in: "path", required: true, schema: new OA\Schema(type: "string", format: "uuid"))
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "name", type: "string"),
                    new OA\Property(property: "price", type: "number"),
                    new OA\Property(property: "description", type: "string"),
                    new OA\Property(property: "is_active", type: "boolean"),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: "Product updated"),
            new OA\Response(response: 404, description: "Product not found"),
            new OA\Response(response: 403, description: "Forbidden")
        ]
    )]
    
    public function update(UpdateProductRequest $request, string $uuid): \Illuminate\Http\JsonResponse
    {
        $product = $this->service->update($uuid, $request->validated());
        return response()->json(ApiResponse::success($product, 'Product updated successfully'));
    }
}