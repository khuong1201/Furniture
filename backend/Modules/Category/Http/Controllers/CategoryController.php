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
use Modules\Category\Http\Resources\CategoryResource; // Import Resource
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
            new OA\Parameter(name: "tree", in: "query", schema: new OA\Schema(type: "boolean"), description: "True: Trả về cây phân cấp. False: Phân trang phẳng"),
            new OA\Parameter(name: "page", in: "query", schema: new OA\Schema(type: "integer")),
            new OA\Parameter(name: "search", in: "query", schema: new OA\Schema(type: "string")),
        ],
        responses: [ 
            new OA\Response(
                response: 200, 
                description: "Success",
                content: new OA\JsonContent(properties: [
                    new OA\Property(property: "success", type: "boolean", example: true),
                    new OA\Property(
                        property: "data", 
                        type: "array", 
                        items: new OA\Items(
                            properties: [
                                new OA\Property(property: "name", type: "string"),
                                new OA\Property(property: "children", type: "array", items: new OA\Items(type: "object"))
                            ]
                        )
                    )
                ])
            ) 
        ]
    )]
    public function index(Request $request): JsonResponse
    {
        // 1. Trường hợp lấy cây thư mục (Recursive Tree)
        if ($request->boolean('tree')) {
            $data = $this->service->getTree();
            // Wrap collection bằng Resource
            return response()->json(ApiResponse::success(CategoryResource::collection($data)));
        }

        // 2. Trường hợp lấy danh sách phẳng (Pagination)
        // Lưu ý: BaseService paginate thường trả về Paginator
        $paginator = $this->service->paginate($request->integer('per_page', 15), $request->all());
        
        // Transform từng item trong paginator
        $paginator->through(fn($category) => new CategoryResource($category));

        return response()->json(ApiResponse::paginated($paginator));
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
        
        $category = $this->service->create($request->validated());
        
        return response()->json(ApiResponse::success(new CategoryResource($category), 'Category created', 201), 201);
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
        
        $updatedCategory = $this->service->update($uuid, $request->validated());

        return response()->json(ApiResponse::success(new CategoryResource($updatedCategory), 'Category updated'));
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
        
        // Eager load children nếu cần hiển thị ngay
        $category->load('children');

        return response()->json(ApiResponse::success(new CategoryResource($category)));
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