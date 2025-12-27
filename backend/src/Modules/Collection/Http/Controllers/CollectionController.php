<?php

declare(strict_types=1);

namespace Modules\Collection\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Modules\Shared\Http\Controllers\BaseController;
use Modules\Shared\Http\Traits\ApiResponseTrait;
use Modules\Collection\Services\CollectionService;
use Modules\Collection\Http\Requests\StoreCollectionRequest;
use Modules\Collection\Http\Requests\UpdateCollectionRequest;
use Modules\Collection\Domain\Models\Collection;
use OpenApi\Attributes as OA;

#[OA\Tag(name: "Collections", description: "API quản lý Bộ sưu tập")]
class CollectionController extends BaseController
{
    public function __construct(CollectionService $service)
    {
        parent::__construct($service);
    }

    #[OA\Get(
        path: "/public/collections",
        summary: "Lấy danh sách bộ sưu tập (Public)",
        tags: ["Collections"],
        parameters: [
            new OA\Parameter(name: "page", in: "query", schema: new OA\Schema(type: "integer")),
            new OA\Parameter(name: "per_page", in: "query", schema: new OA\Schema(type: "integer")),
            new OA\Parameter(name: "search", in: "query", schema: new OA\Schema(type: "string")),
        ],
        responses: [new OA\Response(response: 200, description: "Success")]
    )]
    public function index(Request $request): JsonResponse
    {
        $filters = $request->all();

        // Public chỉ xem active
        if (!$request->user() || !$request->user()->hasPermissionTo('collection.view_all')) {
            $filters['is_active'] = true;
        }

        $data = $this->service->paginate($request->integer('per_page', 10), $filters);
        return $this->successResponse($data);
    }

    #[OA\Get(
        path: "/public/collections/{uuid}",
        summary: "Xem chi tiết bộ sưu tập",
        description: "Trả về thông tin collection và danh sách sản phẩm kèm variant (để lấy giá).",
        tags: ["Collections"],
        parameters: [
            new OA\Parameter(name: "uuid", in: "path", required: true, schema: new OA\Schema(type: "string", format: "uuid"))
        ],
        responses: [new OA\Response(response: 200, description: "Success")]
    )]
    public function show(string $uuid): JsonResponse
    {
        $collection = $this->service->findByUuidOrFail($uuid);
        
        // Eager load products with active check
        $collection->load([
            'products' => function($q) {
                $q->where('is_active', true)
                  ->with(['images', 'variants.attributeValues.attribute']); 
            }
        ]);

        return $this->successResponse($collection);
    }

    #[OA\Post(
        path: "/admin/collections",
        summary: "Tạo bộ sưu tập (Admin)",
        security: [['bearerAuth' => []]],
        tags: ["Collections"],
        requestBody: new OA\RequestBody(
            content: new OA\MediaType(
                mediaType: "multipart/form-data",
                schema: new OA\Schema(
                    required: ["name"],
                    properties: [
                        new OA\Property(property: "name", type: "string"),
                        new OA\Property(property: "slug", type: "string"),
                        new OA\Property(property: "description", type: "string"),
                        new OA\Property(property: "banner_image", type: "string", format: "binary"),
                        new OA\Property(property: "product_ids[]", type: "array", items: new OA\Items(type: "integer")),
                        new OA\Property(property: "is_active", type: "boolean"),
                    ]
                )
            )
        ),
        responses: [new OA\Response(response: 201, description: "Created")]
    )]
    public function store(StoreCollectionRequest $request): JsonResponse
    {
        $this->authorize('create', Collection::class);
        $collection = $this->service->create($request->validated());
        return $this->successResponse($collection, 'Collection created successfully', 201);
    }

    #[OA\Post(
        path: "/admin/collections/{uuid}", 
        summary: "Cập nhật bộ sưu tập (Admin) - Method POST for FormData",
        description: "Sử dụng POST với _method=PUT để upload file trong form-data",
        security: [['bearerAuth' => []]],
        tags: ["Collections"],
        parameters: [
            new OA\Parameter(name: "uuid", in: "path", required: true, schema: new OA\Schema(type: "string", format: "uuid"))
        ],
        requestBody: new OA\RequestBody(
            content: new OA\MediaType(
                mediaType: "multipart/form-data",
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: "_method", type: "string", example: "PUT"),
                        new OA\Property(property: "name", type: "string"),
                        new OA\Property(property: "banner_image", type: "string", format: "binary"),
                        new OA\Property(property: "product_ids[]", type: "array", items: new OA\Items(type: "integer")),
                        new OA\Property(property: "is_active", type: "boolean"),
                    ]
                )
            )
        ),
        responses: [new OA\Response(response: 200, description: "Updated")]
    )]
    public function update(UpdateCollectionRequest $request, string $uuid): JsonResponse
    {
        $collection = $this->service->findByUuidOrFail($uuid);
        $this->authorize('update', $collection);
        $updated = $this->service->update($uuid, $request->validated());
        return $this->successResponse($updated, 'Collection updated successfully');
    }

    #[OA\Delete(
        path: "/admin/collections/{uuid}",
        summary: "Xóa bộ sưu tập (Admin)",
        security: [['bearerAuth' => []]],
        tags: ["Collections"],
        parameters: [
            new OA\Parameter(name: "uuid", in: "path", required: true, schema: new OA\Schema(type: "string", format: "uuid"))
        ],
        responses: [new OA\Response(response: 200, description: "Deleted")]
    )]
    public function destroy(string $uuid): JsonResponse
    {
        $collection = $this->service->findByUuidOrFail($uuid);
        $this->authorize('delete', $collection);
        $this->service->delete($uuid);
        return $this->successResponse(null, 'Collection deleted successfully');
    }
}