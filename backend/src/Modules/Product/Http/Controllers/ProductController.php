<?php

declare(strict_types=1);

namespace Modules\Product\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Modules\Shared\Http\Controllers\BaseController;
use Modules\Shared\Http\Resources\ApiResponse;
use Modules\Product\Http\Resources\ProductResource;
use Modules\Product\Services\ProductService;
use Modules\Product\Domain\Models\Product;
use Modules\Product\Http\Requests\StoreProductRequest;
use Modules\Product\Http\Requests\UpdateProductRequest;
use OpenApi\Attributes as OA;

#[OA\Tag(name: "Products", description: "API quản lý sản phẩm (Hỗ trợ Flash Sale & BigInteger)")]
class ProductController extends BaseController
{
    public function __construct(ProductService $service)
    {
        parent::__construct($service);
    }

    #[OA\Get(
        path: "/public/products",
        summary: "Danh sách sản phẩm (Public)",
        description: "Lấy danh sách sản phẩm active. Hỗ trợ lọc Flash Sale, Giá, Danh mục.",
        tags: ["Products"],
        parameters: [
            new OA\Parameter(name: "page", in: "query", schema: new OA\Schema(type: "integer", default: 1)),
            new OA\Parameter(name: "per_page", in: "query", schema: new OA\Schema(type: "integer", default: 15)),
            new OA\Parameter(name: "search", in: "query", description: "Tìm theo tên/SKU", schema: new OA\Schema(type: "string")),
            new OA\Parameter(name: "category_uuid", in: "query", schema: new OA\Schema(type: "string", format: "uuid")),
            
            // CHANGE: Cập nhật type integer cho BigInteger
            new OA\Parameter(name: "price_min", in: "query", description: "Giá sàn (VND)", schema: new OA\Schema(type: "integer")),
            new OA\Parameter(name: "price_max", in: "query", description: "Giá trần (VND)", schema: new OA\Schema(type: "integer")),
            
            // CHANGE: Thêm tham số này để Swagger UI hiện ô checkbox test
            new OA\Parameter(name: "is_flash_sale", in: "query", description: "Chỉ lấy sản phẩm đang giảm giá", schema: new OA\Schema(type: "boolean")),
            
            new OA\Parameter(name: "sort_by", in: "query", schema: new OA\Schema(type: "string", enum: ["latest", "price_asc", "price_desc", "best_selling"])),
        ],
        responses: [
            new OA\Response(
                response: 200, 
                description: "Success",
                content: new OA\JsonContent(properties: [
                    new OA\Property(property: "success", type: "boolean", example: true),
                    new OA\Property(property: "data", type: "array", items: new OA\Items(type: "object")),
                    new OA\Property(property: "meta", type: "object")
                ])
            )
        ]
    )]
    public function index(Request $request): JsonResponse
    {
        $filters = $request->all();
        
        // Public API luôn chỉ lấy sản phẩm đang active
        $filters['is_active'] = true;

        $perPage = $request->integer('per_page', 15);
        
        // Gọi Service -> Repository (Repository đã handle logic 'is_flash_sale' và eager load 'promotions')
        $paginator = $this->service->paginate($perPage, $filters);
        
        // Áp dụng Resource để tính toán giá hiển thị (Original vs Sale Price)
        $paginator->through(function ($product) {
            return new ProductResource($product);
        });

        return response()->json(ApiResponse::paginated($paginator));
    }

    #[OA\Get(
        path: "/admin/products",
        summary: "Danh sách sản phẩm (Admin)",
        description: "Lấy toàn bộ sản phẩm bao gồm cả ẩn/hiện.",
        security: [['bearerAuth' => []]],
        tags: ["Products"],
        parameters: [
            new OA\Parameter(name: "page", in: "query", schema: new OA\Schema(type: "integer")),
            new OA\Parameter(name: "search", in: "query", schema: new OA\Schema(type: "string")),
            new OA\Parameter(name: "is_active", in: "query", schema: new OA\Schema(type: "boolean")),
            // Admin cũng có thể muốn lọc xem sản phẩm nào đang sale
            new OA\Parameter(name: "is_flash_sale", in: "query", schema: new OA\Schema(type: "boolean")),
        ],
        responses: [
            new OA\Response(response: 200, description: "Success"),
            new OA\Response(response: 403, description: "Forbidden")
        ]
    )]
    public function adminIndex(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Product::class);
        
        $filters = $request->all();
        $perPage = $request->integer('per_page', 15);

        $paginator = $this->service->paginate($perPage, $filters);
        
        $paginator->through(function ($product) {
            return new ProductResource($product);
        });

        return response()->json(ApiResponse::paginated($paginator));
    }

    #[OA\Get(
        path: "/public/products/{uuid}",
        summary: "Chi tiết sản phẩm",
        tags: ["Products"],
        parameters: [
            new OA\Parameter(name: "uuid", in: "path", required: true, schema: new OA\Schema(type: "string", format: "uuid"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Success"),
            new OA\Response(response: 404, description: "Not Found")
        ]
    )]
    public function show(string $uuid): JsonResponse
    {
        // Service tìm product theo UUID
        $product = $this->service->findByUuidOrFail($uuid);
        
        // QUAN TRỌNG: Eager Load để Resource có dữ liệu tính toán
        // Nếu thiếu dòng 'promotions' => fn($q) => $q->active(), Flash Sale sẽ luôn trả về NULL
        $product->load([
            'images', 
            'category', 
            'variants.stock', 
            'variants.attributeValues.attribute',
            
            // Chỉ load khuyến mãi đang chạy (Active + Date Range)
            'promotions' => function($q) { 
                $q->active(); 
            }
        ]);
        
        return response()->json(ApiResponse::success(new ProductResource($product)));
    }

    #[OA\Post(
        path: "/admin/products",
        summary: "Tạo sản phẩm mới",
        security: [['bearerAuth' => []]],
        tags: ["Products"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["name", "category_uuid"],
                properties: [
                    new OA\Property(property: "name", type: "string", example: "Sofa Da Bò Ý"),
                    new OA\Property(property: "category_uuid", type: "string", format: "uuid"),
                    new OA\Property(property: "description", type: "string"),
                    new OA\Property(property: "price", type: "integer", description: "Giá VND (BigInt)"),
                    new OA\Property(property: "has_variants", type: "boolean", default: false),
                    new OA\Property(property: "is_active", type: "boolean", default: true),
                    // Schema variants giữ nguyên...
                    new OA\Property(
                        property: "variants", 
                        type: "array", 
                        items: new OA\Items(
                            properties: [
                                new OA\Property(property: "sku", type: "string"),
                                new OA\Property(property: "price", type: "integer"),
                                new OA\Property(property: "stock", type: "array", items: new OA\Items(properties: [
                                    new OA\Property(property: "warehouse_uuid", type: "string"),
                                    new OA\Property(property: "quantity", type: "integer")
                                ]))
                            ]
                        )
                    )
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: "Created Successfully"),
            new OA\Response(response: 422, description: "Validation Error")
        ]
    )]
    public function store(StoreProductRequest $request): JsonResponse
    {
        $this->authorize('create', Product::class);
        
        $product = $this->service->create($request->validated());
        
        // Load lại relation cần thiết nếu muốn trả về full info ngay sau khi tạo
        $product->load(['category', 'images', 'variants']);

        return response()->json(ApiResponse::success(new ProductResource($product), 'Created', 201));
    }

    #[OA\Put(
        path: "/admin/products/{uuid}",
        summary: "Cập nhật sản phẩm",
        security: [['bearerAuth' => []]],
        tags: ["Products"],
        parameters: [
            new OA\Parameter(name: "uuid", in: "path", required: true, schema: new OA\Schema(type: "string", format: "uuid"))
        ],
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "name", type: "string"),
                    new OA\Property(property: "description", type: "string"),
                    new OA\Property(property: "price", type: "integer"),
                    new OA\Property(property: "is_active", type: "boolean")
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: "Updated Successfully")
        ]
    )]
    public function update(UpdateProductRequest $request, string $uuid): JsonResponse
    {
        $product = $this->service->findByUuidOrFail($uuid);
        
        $this->authorize('update', $product);
        
        $updatedProduct = $this->service->update($uuid, $request->validated());
        
        // Load relation để response đẹp
        $updatedProduct->load(['category', 'images', 'variants', 'promotions' => fn($q) => $q->active()]);

        return response()->json(ApiResponse::success(new ProductResource($updatedProduct), 'Updated'));
    }

    #[OA\Delete(
        path: "/admin/products/{uuid}",
        summary: "Xóa sản phẩm (Soft Delete)",
        security: [['bearerAuth' => []]],
        tags: ["Products"],
        parameters: [
            new OA\Parameter(name: "uuid", in: "path", required: true, schema: new OA\Schema(type: "string", format: "uuid"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Deleted Successfully")
        ]
    )]
    public function destroy(string $uuid): JsonResponse
    {
        $product = $this->service->findByUuidOrFail($uuid);
        
        $this->authorize('delete', $product);
        
        $this->service->delete($uuid);
        
        return response()->json(ApiResponse::success(null, 'Deleted'));
    }
}