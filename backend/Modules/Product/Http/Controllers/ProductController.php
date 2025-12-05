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

#[OA\Tag(name: "Products", description: "API quản lý sản phẩm")]
class ProductController extends BaseController
{
    public function __construct(ProductService $service)
    {
        parent::__construct($service);
    }

    #[OA\Get(
        path: "/public/products",
        summary: "Danh sách sản phẩm (Public)",
        tags: ["Products"],
        parameters: [
            new OA\Parameter(name: "page", in: "query", schema: new OA\Schema(type: "integer")),
            new OA\Parameter(name: "search", in: "query", schema: new OA\Schema(type: "string")),
            new OA\Parameter(name: "category_uuid", in: "query", schema: new OA\Schema(type: "string"))
        ],
        responses: [ new OA\Response(response: 200, description: "Success") ]
    )]
    public function index(Request $request): JsonResponse
    {
        $filters = $request->all();
        $filters['is_active'] = true;
        $data = $this->service->paginate($request->integer('per_page', 15), $filters);
        return response()->json(ApiResponse::paginated($data));
    }

    #[OA\Get(
        path: "/admin/products",
        summary: "Danh sách sản phẩm (Admin)",
        security: [['bearerAuth' => []]],
        tags: ["Products"],
        responses: [ new OA\Response(response: 200, description: "Success") ]
    )]
    public function adminIndex(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Product::class);
        $data = $this->service->paginate($request->integer('per_page', 15), $request->all());
        return response()->json(ApiResponse::paginated($data));
    }

    #[OA\Get(
        path: "/public/products/{uuid}",
        summary: "Chi tiết sản phẩm",
        tags: ["Products"],
        parameters: [ new OA\Parameter(name: "uuid", in: "path", required: true, schema: new OA\Schema(type: "string")) ],
        responses: [ new OA\Response(response: 200, description: "Success") ]
    )]
    public function show(string $uuid): JsonResponse
    {
        $product = $this->service->findByUuidOrFail($uuid);
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
        summary: "Tạo sản phẩm",
        security: [['bearerAuth' => []]],
        tags: ["Products"],
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(
                required: ["name", "category_uuid"],
                properties: [
                    new OA\Property(property: "name", type: "string"),
                    new OA\Property(property: "category_uuid", type: "string"),
                    new OA\Property(property: "price", type: "number"),
                    new OA\Property(property: "has_variants", type: "boolean"),
                    new OA\Property(property: "variants", type: "array", items: new OA\Items(type: "object"))
                ]
            )
        ),
        responses: [ new OA\Response(response: 201, description: "Created") ]
    )]
    public function store(StoreProductRequest $request): JsonResponse
    {
        $this->authorize('create', Product::class);
        $product = $this->service->create($request->validated());
        return response()->json(ApiResponse::success($product, 'Created', 201), 201);
    }

    #[OA\Put(
        path: "/admin/products/{uuid}",
        summary: "Cập nhật sản phẩm",
        security: [['bearerAuth' => []]],
        tags: ["Products"],
        parameters: [ new OA\Parameter(name: "uuid", in: "path", required: true, schema: new OA\Schema(type: "string")) ],
        requestBody: new OA\RequestBody(content: new OA\JsonContent(properties: [ new OA\Property(property: "name", type: "string") ])),
        responses: [ new OA\Response(response: 200, description: "Updated") ]
    )]
    public function update(UpdateProductRequest $request, string $uuid): JsonResponse
    {
        $this->authorize('update', $this->service->findByUuidOrFail($uuid));
        $updated = $this->service->update($uuid, $request->validated());
        return response()->json(ApiResponse::success($updated, 'Updated'));
    }

    #[OA\Delete(
        path: "/admin/products/{uuid}",
        summary: "Xóa sản phẩm",
        security: [['bearerAuth' => []]],
        tags: ["Products"],
        parameters: [ new OA\Parameter(name: "uuid", in: "path", required: true, schema: new OA\Schema(type: "string")) ],
        responses: [ new OA\Response(response: 200, description: "Deleted") ]
    )]
    public function destroy(string $uuid): JsonResponse
    {
        $this->authorize('delete', $this->service->findByUuidOrFail($uuid));
        $this->service->delete($uuid);
        return response()->json(ApiResponse::success(null, 'Deleted'));
    }
}