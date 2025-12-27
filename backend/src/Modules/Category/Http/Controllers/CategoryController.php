<?php

declare(strict_types=1);

namespace Modules\Category\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Modules\Shared\Http\Controllers\BaseController;
use Modules\Shared\Http\Traits\ApiResponseTrait;
use Modules\Category\Services\CategoryService;
use Modules\Category\Http\Requests\StoreCategoryRequest;
use Modules\Category\Http\Requests\UpdateCategoryRequest;
use Modules\Category\Domain\Models\Category;
use Modules\Category\Http\Resources\CategoryResource;
use OpenApi\Attributes as OA;

#[OA\Tag(
    name: "Categories",
    description: "API quản lý Danh mục sản phẩm (Hỗ trợ cấu trúc cây)"
)]
class CategoryController extends BaseController
{
    public function __construct(protected CategoryService $categoryService)
    {
        parent::__construct($categoryService);
    }

    #[OA\Get(
        path: "/api/public/categories",
        summary: "Xem danh sách danh mục (Public)",
        description: "Hỗ trợ 2 chế độ: Cây phân cấp (Tree) hoặc Danh sách phẳng (Pagination).",
        tags: ["Categories"],
        parameters: [
            new OA\Parameter(
                name: "tree", 
                in: "query", 
                schema: new OA\Schema(type: "boolean"), 
                description: "True: Trả về dạng cây (Nested children). False: Trả về dạng phẳng có phân trang."
            ),
            new OA\Parameter(name: "page", in: "query", schema: new OA\Schema(type: "integer", default: 1)),
            new OA\Parameter(name: "per_page", in: "query", schema: new OA\Schema(type: "integer", default: 15)),
            new OA\Parameter(name: "search", in: "query", description: "Tìm theo tên", schema: new OA\Schema(type: "string")),
        ],
        responses: [
            new OA\Response(
                response: 200, 
                description: "Success",
                content: new OA\JsonContent(
                    oneOf: [
                        new OA\Schema(properties: [
                            new OA\Property(property: "success", type: "boolean", example: true),
                            new OA\Property(property: "data", type: "array", items: new OA\Items(properties: [
                                new OA\Property(property: "uuid", type: "string"),
                                new OA\Property(property: "name", type: "string"),
                                new OA\Property(property: "children", type: "array", items: new OA\Items(type: "object"))
                            ]))
                        ]),
                        new OA\Schema(properties: [
                            new OA\Property(property: "success", type: "boolean", example: true),
                            new OA\Property(property: "data", type: "array", items: new OA\Items(type: "object")),
                            new OA\Property(property: "meta", type: "object")
                        ])
                    ]
                )
            )
        ]
    )]
    public function index(Request $request): JsonResponse
    {
        if ($request->boolean('tree')) {
            $data = $this->categoryService->getTree();
            return $this->successResponse(CategoryResource::collection($data));
        }

        $paginator = $this->categoryService->paginate($request->all());
        
        $paginator->through(fn($category) => new CategoryResource($category));

        return $this->successResponse($paginator);
    }

    #[OA\Get(
        path: "/api/public/categories/{uuid}",
        summary: "Chi tiết danh mục",
        tags: ["Categories"],
        parameters: [new OA\Parameter(name: "uuid", in: "path", required: true, schema: new OA\Schema(type: "string", format: "uuid"))],
        responses: [
            new OA\Response(
                response: 200, 
                description: "Success",
                content: new OA\JsonContent(ref: "#/components/schemas/CategoryResource")
            ),
            new OA\Response(response: 404, description: "Not Found (404050)")
        ]
    )]
    public function show(string $uuid): JsonResponse
    {
        $category = $this->categoryService->findByUuidOrFail($uuid);
        $category->load('children'); 

        return $this->successResponse(new CategoryResource($category));
    }

    #[OA\Post(
        path: "/api/admin/categories",
        summary: "Tạo danh mục (Admin)",
        security: [['bearerAuth' => []]],
        tags: ["Categories"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["name"],
                properties: [
                    new OA\Property(property: "name", type: "string", example: "Laptop Gaming"),
                    new OA\Property(property: "slug", type: "string", example: "laptop-gaming", description: "Để trống sẽ tự tạo"),
                    new OA\Property(property: "image", type: "string", example: "https://site.com/icon.png", description: "URL ảnh hoặc icon"),
                    new OA\Property(property: "description", type: "string"),
                    new OA\Property(property: "parent_id", type: "integer", nullable: true, description: "ID của danh mục cha"),
                    new OA\Property(property: "is_active", type: "boolean", default: true)
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: "Created"),
            new OA\Response(response: 409, description: "Duplicate Slug (409051)")
        ]
    )]
    public function store(StoreCategoryRequest $request): JsonResponse
    {
        $this->authorize('create', Category::class);
        $category = $this->categoryService->create($request->validated());
        return $this->successResponse(new CategoryResource($category), 'Category created', 201);
    }

    #[OA\Put(
        path: "/api/admin/categories/{uuid}",
        summary: "Cập nhật danh mục",
        security: [['bearerAuth' => []]],
        tags: ["Categories"],
        parameters: [new OA\Parameter(name: "uuid", in: "path", required: true)],
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "name", type: "string"),
                    new OA\Property(property: "image", type: "string"),
                    new OA\Property(property: "parent_id", type: "integer", nullable: true),
                    new OA\Property(property: "is_active", type: "boolean")
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: "Updated"),
            new OA\Response(
                response: 422, 
                description: "Lỗi Vòng lặp cha con",
                content: new OA\JsonContent(properties: [
                    new OA\Property(property: "success", type: "boolean", example: false),
                    new OA\Property(property: "error_code", type: "integer", example: 422052, description: "Circular reference detected"),
                    new OA\Property(property: "message", type: "string")
                ])
            )
        ]
    )]
    public function update(UpdateCategoryRequest $request, string $uuid): JsonResponse
    {
        $category = $this->categoryService->findByUuidOrFail($uuid);
        $this->authorize('update', $category);
        
        $updatedCategory = $this->categoryService->update($uuid, $request->validated());

        return $this->successResponse(new CategoryResource($updatedCategory), 'Category updated');
    }

    #[OA\Delete(
        path: "/api/admin/categories/{uuid}",
        summary: "Xóa danh mục",
        security: [['bearerAuth' => []]],
        tags: ["Categories"],
        responses: [
            new OA\Response(response: 200, description: "Deleted")
        ]
    )]
    public function destroy(string $uuid): JsonResponse
    {
        $category = $this->categoryService->findByUuidOrFail($uuid);
        $this->authorize('delete', $category);

        $this->categoryService->delete($uuid);
        return $this->successResponse(null, 'Category deleted');
    }
}