<?php

declare(strict_types=1);

namespace Modules\Product\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Modules\Shared\Http\Controllers\BaseController;
use Modules\Shared\Http\Resources\ApiResponse;
use Modules\Product\Services\AttributeService;
use Modules\Product\Http\Requests\StoreAttributeRequest;
use Modules\Product\Http\Requests\UpdateAttributeRequest;
use Modules\Product\Domain\Models\Attribute;
use OpenApi\Attributes as OA;

#[OA\Tag(name: "Product Attributes", description: "API quản lý thuộc tính")]
class AttributeController extends BaseController
{
    public function __construct(AttributeService $service)
    {
        parent::__construct($service);
    }

    #[OA\Get(
        path: "/admin/attributes",
        summary: "Lấy danh sách thuộc tính",
        security: [['bearerAuth' => []]],
        tags: ["Product Attributes"],
        parameters: [
            new OA\Parameter(name: "page", in: "query", schema: new OA\Schema(type: "integer")),
            new OA\Parameter(name: "search", in: "query", schema: new OA\Schema(type: "string"))
        ],
        responses: [ new OA\Response(response: 200, description: "Success") ]
    )]
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Attribute::class);
        $data = $this->service->paginate($request->integer('per_page', 15), $request->all());
        return response()->json(ApiResponse::paginated($data));
    }

    #[OA\Post(
        path: "/admin/attributes",
        summary: "Tạo thuộc tính mới",
        security: [['bearerAuth' => []]],
        tags: ["Product Attributes"],
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(
                required: ["name", "slug", "type"],
                properties: [
                    new OA\Property(property: "name", type: "string", example: "Color"),
                    new OA\Property(property: "slug", type: "string", example: "color"),
                    new OA\Property(property: "type", type: "string", enum: ["text", "select", "color"]),
                    new OA\Property(property: "values", type: "array", items: new OA\Items(properties: [
                        new OA\Property(property: "value", type: "string"),
                        new OA\Property(property: "code", type: "string")
                    ]))
                ]
            )
        ),
        responses: [ new OA\Response(response: 201, description: "Created") ]
    )]
    public function store(StoreAttributeRequest $request): JsonResponse
    {
        $this->authorize('create', Attribute::class);
        $attribute = $this->service->create($request->validated());
        return response()->json(ApiResponse::success($attribute, 'Created', 201), 201);
    }

    #[OA\Get(
        path: "/admin/attributes/{uuid}",
        summary: "Xem chi tiết",
        security: [['bearerAuth' => []]],
        tags: ["Product Attributes"],
        parameters: [ new OA\Parameter(name: "uuid", in: "path", required: true, schema: new OA\Schema(type: "string", format: "uuid")) ],
        responses: [ new OA\Response(response: 200, description: "Success") ]
    )]
    public function show(string $uuid): JsonResponse
    {
        $attribute = $this->service->findByUuidOrFail($uuid);
        $this->authorize('view', $attribute);
        $attribute->load('values');
        return response()->json(ApiResponse::success($attribute));
    }

    #[OA\Put(
        path: "/admin/attributes/{uuid}",
        summary: "Cập nhật thuộc tính",
        security: [['bearerAuth' => []]],
        tags: ["Product Attributes"],
        parameters: [ new OA\Parameter(name: "uuid", in: "path", required: true, schema: new OA\Schema(type: "string", format: "uuid")) ],
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "name", type: "string"),
                    new OA\Property(property: "values", type: "array", items: new OA\Items(type: "object"))
                ]
            )
        ),
        responses: [ new OA\Response(response: 200, description: "Updated") ]
    )]
    public function update(UpdateAttributeRequest $request, string $uuid): JsonResponse
    {
        $attribute = $this->service->findByUuidOrFail($uuid);
        $this->authorize('update', $attribute);
        $updated = $this->service->update($uuid, $request->validated());
        return response()->json(ApiResponse::success($updated, 'Updated'));
    }

    #[OA\Delete(
        path: "/admin/attributes/{uuid}",
        summary: "Xóa thuộc tính",
        security: [['bearerAuth' => []]],
        tags: ["Product Attributes"],
        parameters: [ new OA\Parameter(name: "uuid", in: "path", required: true, schema: new OA\Schema(type: "string", format: "uuid")) ],
        responses: [ new OA\Response(response: 200, description: "Deleted") ]
    )]
    public function destroy(string $uuid): JsonResponse
    {
        $attribute = $this->service->findByUuidOrFail($uuid);
        $this->authorize('delete', $attribute);
        $this->service->delete($uuid);
        return response()->json(ApiResponse::success(null, 'Deleted'));
    }
}