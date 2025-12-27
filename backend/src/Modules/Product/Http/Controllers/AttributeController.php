<?php

declare(strict_types=1);

namespace Modules\Product\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Product\Domain\Models\Attribute;
use Modules\Product\Http\Resources\AttributeResource;
use Modules\Product\Http\Requests\StoreAttributeRequest;
use Modules\Product\Http\Requests\UpdateAttributeRequest;
use Modules\Product\Services\AttributeService;
use Modules\Shared\Http\Controllers\BaseController;
use Modules\Shared\Http\Traits\ApiResponseTrait;
use OpenApi\Attributes as OA;

#[OA\Tag(name: "Product Attributes", description: "Quản lý thuộc tính sản phẩm (Màu sắc, Kích thước...)")]
class AttributeController extends BaseController
{
    public function __construct(AttributeService $service)
    {
        parent::__construct($service);
    }

    #[OA\Get(
        path: "/api/admin/attributes",
        summary: "Lấy danh sách thuộc tính",
        security: [['bearerAuth' => []]],
        tags: ["Product Attributes"],
        parameters: [
            new OA\Parameter(name: "page", in: "query", schema: new OA\Schema(type: "integer", default: 1)),
            new OA\Parameter(name: "per_page", in: "query", schema: new OA\Schema(type: "integer", default: 15)),
            new OA\Parameter(name: "search", in: "query", description: "Tìm theo tên (VD: Color)", schema: new OA\Schema(type: "string"))
        ],
        responses: [
            new OA\Response(
                response: 200, 
                description: "Success",
                content: new OA\JsonContent(properties: [
                    new OA\Property(property: "success", type: "boolean", example: true),
                    new OA\Property(property: "data", type: "array", items: new OA\Items(properties: [
                        new OA\Property(property: "uuid", type: "string"),
                        new OA\Property(property: "name", type: "string"),
                        new OA\Property(property: "values", type: "array", items: new OA\Items(type: "object"))
                    ])),
                    new OA\Property(property: "meta", type: "object")
                ])
            )
        ]
    )]
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Attribute::class);
        $filters = $request->all();
        
        $filters['per_page'] = $request->integer('per_page', 15);

        $data = $this->service->filter($filters);
        return $this->successResponse(AttributeResource::collection($data));
    }

    #[OA\Post(
        path: "/api/admin/attributes",
        summary: "Tạo thuộc tính mới",
        security: [['bearerAuth' => []]],
        tags: ["Product Attributes"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["name", "type"],
                properties: [
                    new OA\Property(property: "name", type: "string", example: "Kích thước"),
                    new OA\Property(property: "slug", type: "string", example: "size"),
                    new OA\Property(property: "type", type: "string", enum: ["text", "select", "color"], default: "select"),
                    new OA\Property(
                        property: "values", 
                        type: "array", 
                        description: "Danh sách giá trị con",
                        items: new OA\Items(properties: [
                            new OA\Property(property: "value", type: "string", example: "XL"),
                            new OA\Property(property: "code", type: "string", example: "SIZE_XL", description: "Mã code hoặc mã màu HEX")
                        ])
                    )
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: "Created"),
            new OA\Response(response: 409, description: "Duplicate Slug (409163)")
        ]
    )]
    public function store(StoreAttributeRequest $request): JsonResponse
    {
        $this->authorize('create', Attribute::class);
        $attribute = $this->service->create($request->validated());
        return $this->successResponse(new AttributeResource($attribute->load('values')), 'Attribute created', 201);
    }

    #[OA\Get(
        path: "/api/admin/attributes/{uuid}",
        summary: "Chi tiết thuộc tính",
        security: [['bearerAuth' => []]],
        tags: ["Product Attributes"],
        parameters: [new OA\Parameter(name: "uuid", in: "path", required: true, schema: new OA\Schema(type: "string"))],
        responses: [new OA\Response(response: 200, description: "OK")]
    )]
    public function show(string $uuid): JsonResponse
    {
        $attribute = $this->service->findByUuidOrFail($uuid);
        $this->authorize('view', $attribute);
        return $this->successResponse(new AttributeResource($attribute->load('values')));
    }

    #[OA\Put(
        path: "/api/admin/attributes/{uuid}",
        summary: "Cập nhật thuộc tính",
        description: "Lưu ý: Mảng 'values' sẽ thay thế hoàn toàn các giá trị cũ.",
        security: [['bearerAuth' => []]],
        tags: ["Product Attributes"],
        parameters: [new OA\Parameter(name: "uuid", in: "path", required: true)],
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "name", type: "string"),
                    new OA\Property(property: "values", type: "array", items: new OA\Items(type: "object"))
                ]
            )
        ),
        responses: [new OA\Response(response: 200, description: "Updated")]
    )]
    public function update(UpdateAttributeRequest $request, string $uuid): JsonResponse
    {
        $attribute = $this->service->findByUuidOrFail($uuid);
        $this->authorize('update', $attribute);
        
        $updated = $this->service->update($uuid, $request->validated());
        return $this->successResponse(new AttributeResource($updated->load('values')), 'Attribute updated');
    }

    #[OA\Delete(
        path: "/api/admin/attributes/{uuid}",
        summary: "Xóa thuộc tính",
        security: [['bearerAuth' => []]],
        tags: ["Product Attributes"],
        parameters: [new OA\Parameter(name: "uuid", in: "path", required: true)],
        responses: [
            new OA\Response(response: 200, description: "Deleted"),
            new OA\Response(response: 409, description: "Đang được sử dụng bởi sản phẩm")
        ]
    )]
    public function destroy(string $uuid): JsonResponse
    {
        $attribute = $this->service->findByUuidOrFail($uuid);
        $this->authorize('delete', $attribute);
        
        $this->service->delete($uuid);
        return $this->successResponse(null, 'Attribute deleted');

    }
}