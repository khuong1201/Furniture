<?php

declare(strict_types=1);

namespace Modules\Product\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Modules\Shared\Http\Controllers\BaseController;
use Modules\Product\Http\Resources\ProductResource;
use Modules\Product\Services\ProductService;
use Modules\Product\Domain\Models\Product;
use Modules\Product\Http\Requests\StoreProductRequest;
use Modules\Product\Http\Requests\UpdateProductRequest;
use OpenApi\Attributes as OA;

#[OA\Tag(name: "Products", description: "API quản lý sản phẩm")]
class ProductController extends BaseController
{
    protected ProductService $productService;

    public function __construct(ProductService $service)
    {
        parent::__construct($service);
        $this->productService = $service;
    }

    #[OA\Post(
        path: "/api/admin/products/generate-ai-description",
        summary: "Tạo mô tả sản phẩm bằng AI",
        security: [['bearerAuth' => []]],
        tags: ["Products"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(properties: [
                new OA\Property(property: "name", type: "string", example: "Áo thun nam"),
                new OA\Property(property: "category_uuid", type: "string"),
                new OA\Property(property: "brand_uuid", type: "string"),
                new OA\Property(property: "price", type: "integer"),
                new OA\Property(property: "variants", type: "array", items: new OA\Items(type: "object"))
            ])
        ),
        responses: [
            new OA\Response(
                response: 200, 
                description: "Success",
                content: new OA\JsonContent(properties: [
                    new OA\Property(property: "description", type: "string")
                ])
            )
        ]
    )]
    public function generateDescription(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'category_uuid' => 'nullable|string',
            'brand_uuid' => 'nullable|string',
        ]);

        $description = $this->productService->generateAiDescription($request->all());

        return response()->json([
            'success' => true,
            'data' => [
                'description' => $description
            ]
        ]);
    }

    #[OA\Get(
        path: "/api/public/products",
        summary: "Danh sách sản phẩm (Public)",
        description: "API dành cho trang chủ/trang danh sách. Chỉ trả về sản phẩm đang Active.",
        tags: ["Products"],
        parameters: [
            new OA\Parameter(name: "page", in: "query", schema: new OA\Schema(type: "integer", default: 1)),
            new OA\Parameter(name: "per_page", in: "query", schema: new OA\Schema(type: "integer", default: 15)),
            new OA\Parameter(name: "search", in: "query", description: "Tìm theo tên hoặc SKU", schema: new OA\Schema(type: "string")),
            new OA\Parameter(name: "category_slug", in: "query", description: "Lọc theo danh mục (slug)", schema: new OA\Schema(type: "string")),
            new OA\Parameter(name: "price_min", in: "query", description: "Giá sàn (VND)", schema: new OA\Schema(type: "integer")),
            new OA\Parameter(name: "price_max", in: "query", description: "Giá trần (VND)", schema: new OA\Schema(type: "integer")),
            new OA\Parameter(name: "sort_by", in: "query", description: "Sắp xếp", schema: new OA\Schema(type: "string", enum: ["latest", "price_asc", "price_desc", "best_selling"])),
        ],
        responses: [
            new OA\Response(
                response: 200, 
                description: "Success",
                content: new OA\JsonContent(properties: [
                    new OA\Property(property: "success", type: "boolean", example: true),
                    new OA\Property(property: "data", type: "array", items: new OA\Items(ref: "#/components/schemas/ProductResource")),
                    new OA\Property(property: "meta", type: "object")
                ])
            )
        ]
    )]
    public function index(Request $request): JsonResponse
    {
        $filters = $request->all();
        $filters['is_active'] = true; // Public luôn chỉ lấy active

        $paginator = $this->productService->filter($request->integer('per_page', 15), $filters);
        $paginator->through(fn($product) => new ProductResource($product));

        return $this->successResponse($paginator);
    }

    #[OA\Get(
        path: "/api/admin/products",
        summary: "Danh sách sản phẩm (Admin)",
        description: "Dành cho CMS. Lấy cả sản phẩm ẩn/hiện.",
        security: [['bearerAuth' => []]],
        tags: ["Products"],
        parameters: [
            new OA\Parameter(name: "page", in: "query", schema: new OA\Schema(type: "integer")),
            new OA\Parameter(name: "search", in: "query", schema: new OA\Schema(type: "string")),
            new OA\Parameter(name: "is_active", in: "query", description: "Lọc trạng thái ẩn hiện", schema: new OA\Schema(type: "boolean")),
        ],
        responses: [new OA\Response(response: 200, description: "Success")]
    )]
    public function adminIndex(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Product::class);
        
        $paginator = $this->productService->filter($request->integer('per_page', 15), $request->all());
        $paginator->through(fn($product) => new ProductResource($product));

        return $this->successResponse($paginator);
    }

    #[OA\Get(
        path: "/api/public/products/{uuid}",
        summary: "Chi tiết sản phẩm",
        tags: ["Products"],
        parameters: [new OA\Parameter(name: "uuid", in: "path", required: true, schema: new OA\Schema(type: "string"))],
        responses: [
            new OA\Response(response: 200, description: "Success", content: new OA\JsonContent(ref: "#/components/schemas/ProductResource")),
            new OA\Response(response: 404, description: "Product Not Found")
        ]
    )]
    public function show(string $uuid): JsonResponse
    {
        $product = $this->productService->findByUuidOrFail($uuid);
        
        // Load các relation cần thiết để hiển thị chi tiết
        $product->load([
            'images', 
            'category', 
            'brand',
            'variants.stock', 
            'variants.attributeValues.attribute',
            'promotions' => fn($q) => $q->active()
        ]);
        
        return $this->successResponse(new ProductResource($product));
    }

    #[OA\Post(
        path: "/api/admin/products",
        summary: "Tạo sản phẩm mới",
        security: [['bearerAuth' => []]],
        tags: ["Products"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: "#/components/schemas/StoreProductRequest") // Giả sử bạn đã define schema này hoặc dùng properties trực tiếp
        ),
        responses: [
            new OA\Response(response: 201, description: "Product Created"),
            new OA\Response(response: 422, description: "Validation Error")
        ]
    )]
    public function store(StoreProductRequest $request): JsonResponse
    {
        $this->authorize('create', Product::class);
        
        // $request->validated() sẽ chứa cả slug, brand_id, attributes... nhờ file Request đã update
        $product = $this->productService->create($request->validated());
        
        return $this->successResponse(new ProductResource($product), 'Tạo sản phẩm thành công', 201);
    }

    #[OA\Put(
        path: "/api/admin/products/{uuid}",
        summary: "Cập nhật sản phẩm",
        security: [['bearerAuth' => []]],
        tags: ["Products"],
        parameters: [new OA\Parameter(name: "uuid", in: "path", required: true)],
        responses: [new OA\Response(response: 200, description: "Updated")]
    )]
    public function update(UpdateProductRequest $request, string $uuid): JsonResponse
    {
        \Log::info("Update Product Request:", ['uuid' => $uuid]);
        // Check quyền trên Model cụ thể (nếu cần)
        $product = $this->productService->findByUuidOrFail($uuid);
        $this->authorize('update', $product);

        $updated = $this->productService->update($uuid, $request->validated());
        
        $updated->load(['category', 'images', 'variants', 'promotions' => fn($q) => $q->active()]);

        return $this->successResponse(new ProductResource($updated), 'Cập nhật thành công');
    }

    #[OA\Delete(
        path: "/api/admin/products/{uuid}",
        summary: "Xóa sản phẩm (Soft Delete)",
        security: [['bearerAuth' => []]],
        tags: ["Products"],
        responses: [new OA\Response(response: 200, description: "Deleted")]
    )]
    public function destroy(string $uuid): JsonResponse
    {
        $product = $this->productService->findByUuidOrFail($uuid);
        $this->authorize('delete', $product);
        
        $this->productService->delete($uuid);
        
        return $this->successResponse(null, 'Xóa sản phẩm thành công');
    }
}