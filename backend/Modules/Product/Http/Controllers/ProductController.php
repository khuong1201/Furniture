<?php

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

#[OA\Tag(
    name: "Products",
    description: "API quản lý sản phẩm (Hỗ trợ biến thể/Variant)"
)]
class ProductController extends BaseController
{
    public function __construct(ProductService $service)
    {
        parent::__construct($service);
    }

    #[OA\Get(
        path: "/public/products",
        summary: "Lấy danh sách sản phẩm (Public)",
        description: "Chỉ trả về các sản phẩm đang hoạt động (Active). Hỗ trợ lọc theo danh mục, giá, tên.",
        tags: ["Products"],
        parameters: [
            new OA\Parameter(name: "page", in: "query", schema: new OA\Schema(type: "integer", default: 1)),
            new OA\Parameter(name: "per_page", in: "query", schema: new OA\Schema(type: "integer", default: 15)),
            new OA\Parameter(name: "search", in: "query", description: "Tìm theo tên hoặc SKU", schema: new OA\Schema(type: "string")),
            new OA\Parameter(name: "category_uuid", in: "query", description: "Lọc theo danh mục", schema: new OA\Schema(type: "string", format: "uuid")),
            new OA\Parameter(name: "price_min", in: "query", schema: new OA\Schema(type: "number")),
            new OA\Parameter(name: "price_max", in: "query", schema: new OA\Schema(type: "number")),
        ],
        responses: [
            new OA\Response(response: 200, description: "Success", content: new OA\JsonContent(properties: [
                new OA\Property(property: "success", type: "boolean", example: true),
                new OA\Property(property: "data", type: "object", description: "Paginated Data")
            ]))
        ]
    )]
    public function index(Request $request): JsonResponse
    {

        $filters = $request->all();

        $filters['is_active'] = true;

        $data = $this->service->paginate($request->get('per_page', 15), $filters);
        
        return response()->json(ApiResponse::paginated($data));
    }

    #[OA\Get(
        path: "/admin/products",
        summary: "Lấy danh sách sản phẩm (Admin)",
        description: "Trả về tất cả sản phẩm bao gồm cả ẩn (Inactive). Dành cho trang quản lý.",
        security: [['bearerAuth' => []]],
        tags: ["Products"],
        parameters: [
            new OA\Parameter(name: "page", in: "query", schema: new OA\Schema(type: "integer")),
            new OA\Parameter(name: "per_page", in: "query", schema: new OA\Schema(type: "integer")),
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

        if (!$request->user()->hasPermissionTo('product.view')) {
             return response()->json(ApiResponse::error('Forbidden', 403), 403);
        }

        $data = $this->service->paginate($request->get('per_page', 15), $request->all());
        
        return response()->json(ApiResponse::paginated($data));
    }

    #[OA\Get(
        path: "/public/products/{uuid}",
        summary: "Xem chi tiết sản phẩm",
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
        
        $product->load([
            'images', 
            'category', 
            'variants.attributeValues.attribute', 
            'variants.stock.warehouse',
            'promotions'
        ]);
        
        $product->loadAvg('reviews', 'rating');

        return response()->json(ApiResponse::success(new ProductResource($product)));
    }

    #[OA\Post(
        path: "/admin/products",
        summary: "Tạo sản phẩm mới",
        description: "Tạo sản phẩm đơn giản hoặc sản phẩm có biến thể.",
        security: [['bearerAuth' => []]],
        tags: ["Products"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["name", "category_uuid"],
                properties: [
                    new OA\Property(property: "name", type: "string", example: "Áo Thun Premium"),
                    new OA\Property(property: "category_uuid", type: "string", format: "uuid"),
                    new OA\Property(property: "description", type: "string"),
                    new OA\Property(property: "has_variants", type: "boolean", example: true),
                    new OA\Property(property: "is_active", type: "boolean", default: true),

                    new OA\Property(property: "price", type: "number", description: "Bắt buộc nếu has_variants=false"),
                    new OA\Property(property: "sku", type: "string", description: "Bắt buộc nếu has_variants=false"),
                
                    new OA\Property(
                        property: "variants", 
                        type: "array", 
                        items: new OA\Items(
                            type: "object",
                            properties: [
                                new OA\Property(property: "sku", type: "string"),
                                new OA\Property(property: "price", type: "number"),
                                new OA\Property(property: "attributes", type: "array", items: new OA\Items(type: "string", format: "uuid"), description: "Mảng UUID của Attribute Values (VD: Màu Đỏ, Size M)"),
                                new OA\Property(property: "stock", type: "array", items: new OA\Items(properties: [
                                    new OA\Property(property: "warehouse_uuid", type: "string", format: "uuid"),
                                    new OA\Property(property: "quantity", type: "integer")
                                ]))
                            ]
                        )
                    )
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: "Created"),
            new OA\Response(response: 422, description: "Validation Error"),
            new OA\Response(response: 403, description: "Forbidden")
        ]
    )]
    public function store(StoreProductRequest $request): JsonResponse
    {
        $this->authorize('create', Product::class);

        $product = $this->service->create($request->validated());
        
        return response()->json(ApiResponse::success($product, 'Product created successfully', 201), 201);
    }

    #[OA\Put(
        path: "/admin/products/{uuid}",
        summary: "Cập nhật sản phẩm",
        description: "Cập nhật thông tin chung và đồng bộ biến thể (Thêm/Sửa/Xóa Variant).",
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
                    new OA\Property(property: "is_active", type: "boolean"),
                    new OA\Property(property: "category_uuid", type: "string", format: "uuid"),
                    new OA\Property(property: "variants", type: "array", description: "Gửi mảng variants đầy đủ. Có UUID là update, không có là create. Thiếu là delete.", items: new OA\Items(type: "object"))
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: "Updated"),
            new OA\Response(response: 403, description: "Forbidden"),
            new OA\Response(response: 404, description: "Not Found")
        ]
    )]
    public function update(UpdateProductRequest $request, string $uuid): JsonResponse
    {
        $product = $this->service->findByUuidOrFail($uuid);
        
        $this->authorize('update', $product);

        $updatedProduct = $this->service->update($uuid, $request->validated());
        
        return response()->json(ApiResponse::success($updatedProduct, 'Product updated successfully'));
    }

    #[OA\Delete(
        path: "/admin/products/{uuid}",
        summary: "Xóa sản phẩm",
        security: [['bearerAuth' => []]],
        tags: ["Products"],
        parameters: [
            new OA\Parameter(name: "uuid", in: "path", required: true, schema: new OA\Schema(type: "string", format: "uuid"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Deleted"),
            new OA\Response(response: 403, description: "Forbidden"),
            new OA\Response(response: 404, description: "Not Found")
        ]
    )]
    public function destroy(string $uuid): JsonResponse
    {
        $product = $this->service->findByUuidOrFail($uuid);
        
        $this->authorize('delete', $product);

        $this->service->delete($uuid);
        
        return response()->json(ApiResponse::success(null, 'Product deleted successfully'));
    }
}