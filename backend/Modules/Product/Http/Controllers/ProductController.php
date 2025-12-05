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

#[OA\Tag(name: "Products", description: "API quản lý sản phẩm (Sofa, Bàn, Ghế...)")]
class ProductController extends BaseController
{
    public function __construct(ProductService $service)
    {
        parent::__construct($service);
    }

    #[OA\Get(
        path: "/public/products",
        summary: "Danh sách sản phẩm (Public)",
        description: "Lấy danh sách sản phẩm đang hoạt động (Active). Có thể lọc theo tên, danh mục, khoảng giá.",
        tags: ["Products"],
        parameters: [
            new OA\Parameter(name: "page", in: "query", description: "Trang hiện tại", schema: new OA\Schema(type: "integer", default: 1)),
            new OA\Parameter(name: "per_page", in: "query", description: "Số lượng item/trang", schema: new OA\Schema(type: "integer", default: 15)),
            new OA\Parameter(name: "search", in: "query", description: "Tìm kiếm theo tên hoặc SKU", schema: new OA\Schema(type: "string")),
            new OA\Parameter(name: "category_uuid", in: "query", description: "Lọc theo danh mục", schema: new OA\Schema(type: "string", format: "uuid")),
            new OA\Parameter(name: "price_min", in: "query", description: "Giá thấp nhất", schema: new OA\Schema(type: "number")),
            new OA\Parameter(name: "price_max", in: "query", description: "Giá cao nhất", schema: new OA\Schema(type: "number")),
            new OA\Parameter(name: "sort_by", in: "query", description: "Sắp xếp (latest, price_asc, price_desc, best_selling)", schema: new OA\Schema(type: "string", enum: ["latest", "price_asc", "price_desc", "best_selling"])),
        ],
        responses: [
            new OA\Response(
                response: 200, 
                description: "Success",
                content: new OA\JsonContent(properties: [
                    new OA\Property(property: "success", type: "boolean", example: true),
                    new OA\Property(property: "data", type: "array", items: new OA\Items(type: "object")), // Product Resource structure
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
        
        // Gọi Service để lấy Paginator (Chứa Model)
        $paginator = $this->service->paginate($perPage, $filters);
        
        // QUAN TRỌNG: Áp dụng ProductResource lên từng item trong kết quả phân trang
        // Để đảm bảo giá tiền được quy đổi (Currency) và format đúng chuẩn
        $paginator->through(function ($product) {
            return new ProductResource($product);
        });

        return response()->json(ApiResponse::paginated($paginator));
    }

    #[OA\Get(
        path: "/admin/products",
        summary: "Danh sách sản phẩm (Admin)",
        description: "Lấy toàn bộ sản phẩm bao gồm cả ẩn/hiện để quản lý.",
        security: [['bearerAuth' => []]],
        tags: ["Products"],
        parameters: [
            new OA\Parameter(name: "page", in: "query", schema: new OA\Schema(type: "integer")),
            new OA\Parameter(name: "search", in: "query", schema: new OA\Schema(type: "string")),
            new OA\Parameter(name: "is_active", in: "query", schema: new OA\Schema(type: "boolean")),
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
        
        // Áp dụng Resource cho Admin luôn để format dữ liệu đồng nhất
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
        $product = $this->service->findByUuidOrFail($uuid);
        
        // Eager Load các quan hệ cần thiết để Resource xử lý
        // - images: Ảnh sản phẩm
        // - category: Danh mục
        // - variants.stock: Tồn kho của từng biến thể (Inventory Module)
        // - variants.attributeValues.attribute: Thuộc tính (Màu, Size...)
        // - promotions: Khuyến mãi đang chạy
        $product->load([
            'images', 
            'category', 
            'variants.stock', 
            'variants.attributeValues.attribute',
            'promotions' => function($q) { $q->active(); }
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
                    new OA\Property(property: "price", type: "number", description: "Giá (nếu không có biến thể)"),
                    new OA\Property(property: "has_variants", type: "boolean", default: false),
                    new OA\Property(property: "is_active", type: "boolean", default: true),
                    new OA\Property(
                        property: "variants", 
                        type: "array", 
                        items: new OA\Items(
                            properties: [
                                new OA\Property(property: "sku", type: "string"),
                                new OA\Property(property: "price", type: "number"),
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
            new OA\Response(response: 422, description: "Validation Error"),
            new OA\Response(response: 403, description: "Forbidden")
        ]
    )]
    public function store(StoreProductRequest $request): JsonResponse
    {
        $this->authorize('create', Product::class);
        
        $product = $this->service->create($request->validated());
        
        // Trả về Resource để đảm bảo cấu trúc dữ liệu trả về chuẩn
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
                    new OA\Property(property: "price", type: "number"),
                    new OA\Property(property: "is_active", type: "boolean")
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: "Updated Successfully"),
            new OA\Response(response: 404, description: "Not Found")
        ]
    )]
    public function update(UpdateProductRequest $request, string $uuid): JsonResponse
    {
        $product = $this->service->findByUuidOrFail($uuid);
        
        $this->authorize('update', $product);
        
        $updatedProduct = $this->service->update($uuid, $request->validated());
        
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
            new OA\Response(response: 200, description: "Deleted Successfully"),
            new OA\Response(response: 403, description: "Forbidden")
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