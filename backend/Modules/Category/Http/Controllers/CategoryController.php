<?php

namespace Modules\Category\Http\Controllers;

use Illuminate\Http\Request;
use Modules\Shared\Http\Controllers\BaseController;
use Modules\Shared\Http\Resources\ApiResponse;
use Modules\Category\Services\CategoryService;
use Modules\Category\Http\Requests\StoreCategoryRequest;
use Modules\Category\Http\Requests\UpdateCategoryRequest;
use Modules\Category\Domain\Models\Category; 
use OpenApi\Attributes as OA;

#[OA\Tag(
    name: "Categories",
    description: "API quản lý Danh mục sản phẩm (Public xem, Admin quản lý)"
)]
class CategoryController extends BaseController
{
    public function __construct(CategoryService $service)
    {
        parent::__construct($service);
    }

    #[OA\Get(
        path: "/api/public/categories",
        summary: "Xem danh sách danh mục (Public)",
        tags: ["Categories"],
        parameters: [
            new OA\Parameter(name: "tree", in: "query", description: "Trả về dưới dạng cây (true/false)", required: false, schema: new OA\Schema(type: "boolean")),
            new OA\Parameter(name: "page", in: "query", description: "Page number", required: false, schema: new OA\Schema(type: "integer", default: 1)),
        ],
        responses: [
            new OA\Response(response: 200, description: "Successful operation")
        ]
    )]
    public function index(Request $request): \Illuminate\Http\JsonResponse
    {
        if ($request->has('tree')) {
            $data = $this->service->getTree();
            return response()->json(ApiResponse::success($data));
        }

        return parent::index($request);
    }

    #[OA\Post(
        path: "/api/admin/categories",
        summary: "Tạo danh mục mới (Admin)",
        security: [['bearerAuth' => []]],
        tags: ["Categories"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["name", "slug"],
                properties: [
                    new OA\Property(property: "name", type: "string", example: "Điện thoại thông minh"),
                    new OA\Property(property: "slug", type: "string", example: "dien-thoai-thong-minh"),
                    new OA\Property(property: "parent_uuid", type: "string", format: "uuid", nullable: true, description: "UUID của danh mục cha"),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: "Category created"),
            new OA\Response(response: 403, description: "Forbidden (Not Admin)")
        ]
    )]
    public function store(StoreCategoryRequest $request): \Illuminate\Http\JsonResponse
    {
        $this->authorize('create', Category::class);
        
        $data = $this->service->create($request->validated());
        
        return response()->json(ApiResponse::success($data, 'Category created', 201), 201);
    }

    #[OA\Put(
        path: "/api/admin/categories/{uuid}",
        summary: "Cập nhật danh mục (Admin)",
        security: [['bearerAuth' => []]],
        tags: ["Categories"],
        parameters: [
            new OA\Parameter(name: "uuid", in: "path", required: true, schema: new OA\Schema(type: "string", format: "uuid"))
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "name", type: "string", example: "Điện thoại thông minh (Updated)"),
                    new OA\Property(property: "slug", type: "string", example: "dien-thoai-thong-minh-updated"),
                    new OA\Property(property: "parent_uuid", type: "string", format: "uuid", nullable: true),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: "Category updated"),
            new OA\Response(response: 403, description: "Forbidden"),
            new OA\Response(response: 404, description: "Category not found")
        ]
    )]
    public function update(UpdateCategoryRequest $request, string $uuid): \Illuminate\Http\JsonResponse
    {
        $category = $this->service->getRepository()->findByUuid($uuid);
        if (!$category) {
             return response()->json(ApiResponse::error('Category not found', 404), 404);
        }
        $this->authorize('update', $category);
        
        $data = $this->service->update($uuid, $request->validated());

        return response()->json(ApiResponse::success($data, 'Category updated'));
    }
    
    #[OA\Get(
        path: "/api/public/categories/{uuid}",
        summary: "Xem chi tiết danh mục (Public)",
        tags: ["Categories"],
        parameters: [
            new OA\Parameter(name: "uuid", in: "path", required: true, schema: new OA\Schema(type: "string", format: "uuid"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Successful operation"),
            new OA\Response(response: 404, description: "Category not found")
        ]
    )]
    public function show(string $uuid): \Illuminate\Http\JsonResponse
    {
        $category = $this->service->findByUuidOrFail($uuid);
        $this->authorize('view', $category);

        return response()->json(ApiResponse::success($category));
    }

    #[OA\Delete(
        path: "/api/admin/categories/{uuid}",
        summary: "Xóa danh mục (Admin)",
        security: [['bearerAuth' => []]],
        tags: ["Categories"],
        parameters: [
            new OA\Parameter(name: "uuid", in: "path", required: true, schema: new OA\Schema(type: "string", format: "uuid"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Category deleted"),
            new OA\Response(response: 403, description: "Forbidden"),
            new OA\Response(response: 404, description: "Category not found")
        ]
    )]
    public function destroy(string $uuid): \Illuminate\Http\JsonResponse
    {
        $category = $this->service->getRepository()->findByUuid($uuid);
        if (!$category) {
             return response()->json(ApiResponse::error('Category not found', 404), 404);
        }
        $this->authorize('delete', $category);

        $this->service->delete($uuid);
        return response()->json(ApiResponse::success(null, 'Category deleted'));
    }
}