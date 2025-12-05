<?php

declare(strict_types=1);

namespace Modules\Category\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Modules\Shared\Http\Controllers\BaseController;
use Modules\Shared\Http\Resources\ApiResponse;
use Modules\Category\Services\CategoryService;
use Modules\Category\Http\Requests\StoreCategoryRequest;
use Modules\Category\Http\Requests\UpdateCategoryRequest;
use Modules\Category\Domain\Models\Category;
use OpenApi\Attributes as OA;

#[OA\Tag(
    name: "Categories",
    description: "API quản lý Danh mục sản phẩm"
)]
class CategoryController extends BaseController
{
    public function __construct(CategoryService $service)
    {
        parent::__construct($service);
    }

    #[OA\Get(
        path: "/public/categories",
        summary: "Xem danh sách danh mục (Public)",
        tags: ["Categories"],
        parameters: [
            new OA\Parameter(name: "tree", in: "query", schema: new OA\Schema(type: "boolean"), description: "Trả về dạng cây phân cấp"),
            new OA\Parameter(name: "page", in: "query", schema: new OA\Schema(type: "integer")),
            new OA\Parameter(name: "search", in: "query", schema: new OA\Schema(type: "string")),
        ],
        responses: [ new OA\Response(response: 200, description: "Success") ]
    )]
    public function index(Request $request): JsonResponse
    {
        if ($request->boolean('tree')) {
            $data = $this->service->getTree();
            return response()->json(ApiResponse::success($data));
        }

        return parent::index($request);
    }

    #[OA\Post(
        path: "/admin/categories",
        summary: "Tạo danh mục mới (Admin)",
        security: [['bearerAuth' => []]],
        tags: ["Categories"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["name"],
                properties: [
                    new OA\Property(property: "name", type: "string", example: "Electronics"),
                    new OA\Property(property: "slug", type: "string", example: "electronics"),
                    new OA\Property(property: "parent_id", type: "integer", example: 1),
                    new OA\Property(property: "description", type: "string"),
                    new OA\Property(property: "is_active", type: "boolean", default: true)
                ]
            )
        ),
        responses: [ new OA\Response(response: 201, description: "Created") ]
    )]
    public function store(StoreCategoryRequest $request): JsonResponse
    {
        $this->authorize('create', Category::class);
        
        $data = $this->service->create($request->validated());
        
        return response()->json(ApiResponse::success($data, 'Category created', 201), 201);
    }

    #[OA\Put(
        path: "/admin/categories/{uuid}",
        summary: "Cập nhật danh mục (Admin)",
        security: [['bearerAuth' => []]],
        tags: ["Categories"],
        parameters: [
            new OA\Parameter(name: "uuid", in: "path", required: true, schema: new OA\Schema(type: "string", format: "uuid"))
        ],
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "name", type: "string"),
                    new OA\Property(property: "parent_id", type: "integer"),
                    new OA\Property(property: "description", type: "string"),
                    new OA\Property(property: "is_active", type: "boolean")
                ]
            )
        ),
        responses: [ new OA\Response(response: 200, description: "Updated") ]
    )]
    public function update(UpdateCategoryRequest $request, string $uuid): JsonResponse
    {
        $category = $this->service->findByUuidOrFail($uuid);
        $this->authorize('update', $category);
        
        $data = $this->service->update($uuid, $request->validated());

        return response()->json(ApiResponse::success($data, 'Category updated'));
    }
    
    #[OA\Get(
        path: "/public/categories/{uuid}",
        summary: "Xem chi tiết danh mục",
        tags: ["Categories"],
        parameters: [
            new OA\Parameter(name: "uuid", in: "path", required: true, schema: new OA\Schema(type: "string", format: "uuid"))
        ],
        responses: [ new OA\Response(response: 200, description: "Success") ]
    )]
    public function show(string $uuid): JsonResponse
    {
        $category = $this->service->findByUuidOrFail($uuid);
        return response()->json(ApiResponse::success($category));
    }

    #[OA\Delete(
        path: "/admin/categories/{uuid}",
        summary: "Xóa danh mục (Admin)",
        security: [['bearerAuth' => []]],
        tags: ["Categories"],
        parameters: [
            new OA\Parameter(name: "uuid", in: "path", required: true, schema: new OA\Schema(type: "string", format: "uuid"))
        ],
        responses: [ new OA\Response(response: 200, description: "Deleted") ]
    )]
    public function destroy(string $uuid): JsonResponse
    {
        $category = $this->service->findByUuidOrFail($uuid);
        $this->authorize('delete', $category);

        $this->service->delete($uuid);
        return response()->json(ApiResponse::success(null, 'Category deleted'));
    }
}