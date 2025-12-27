<?php

declare(strict_types=1);

namespace Modules\Shipping\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Modules\Shared\Http\Controllers\BaseController;
use Modules\Shipping\Services\ShippingService;
use Modules\Shipping\Domain\Models\Snipping;
use Modules\Shipping\Http\Requests\StoreShippingRequest;
use Modules\Shipping\Http\Requests\UpdateShippingRequest;
use Modules\Shipping\Http\Resources\ShippingResource;
use OpenApi\Attributes as OA;

#[OA\Tag(name: "Shipping", description: "API quản lý Vận chuyển")]
class ShippingController extends BaseController
{
    public function __construct(ShippingService $service)
    {
        parent::__construct($service);
    }

    #[OA\Get(
        path: "/api/admin/shippings",
        summary: "Xem danh sách vận đơn",
        security: [['bearerAuth' => []]],
        tags: ["Shipping"],
        parameters: [
            new OA\Parameter(name: "page", in: "query", schema: new OA\Schema(type: "integer")),
            new OA\Parameter(name: "tracking_number", in: "query", schema: new OA\Schema(type: "string")),
            new OA\Parameter(name: "status", in: "query", schema: new OA\Schema(type: "string")),
        ],
        responses: [
            new OA\Response(
                response: 200, 
                description: "Success",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(
                            property: "data", 
                            type: "array", 
                            items: new OA\Items(ref: "#/components/schemas/ShippingResource") // Refer to Resource Schema
                        ),
                        new OA\Property(property: "meta", type: "object")
                    ]
                )
            )
        ]
    )]
    public function index(Request $request): JsonResponse
    {
        // $this->authorize('viewAny', Shipping::class);
        $data = $this->service->paginate($request->integer('per_page', 15), $request->all());
        
        // Dùng Resource Collection
        return $this->successResponse(ShippingResource::collection($data));
    }

    #[OA\Post(
        path: "/api/admin/shippings",
        summary: "Tạo vận đơn mới",
        security: [['bearerAuth' => []]],
        tags: ["Shipping"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["order_uuid", "provider", "tracking_number"],
                properties: [
                    new OA\Property(property: "order_uuid", type: "string", format: "uuid"),
                    new OA\Property(property: "provider", type: "string", example: "GHTK"),
                    new OA\Property(property: "tracking_number", type: "string", example: "S88888888"),
                    new OA\Property(property: "fee", type: "number", example: 30000),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201, 
                description: "Created",
                content: new OA\JsonContent(ref: "#/components/schemas/ShippingResource")
            )
        ]
    )]
    public function store(StoreShippingRequest $request): JsonResponse
    {
        // $this->authorize('create', Shipping::class);
        $shipping = $this->service->create($request->validated());
        return $this->successResponse(new ShippingResource($shipping), 'Created', 201);
    }

    #[OA\Get(
        path: "/api/admin/shippings/{uuid}",
        summary: "Xem chi tiết",
        security: [['bearerAuth' => []]],
        tags: ["Shipping"],
        parameters: [new OA\Parameter(name: "uuid", in: "path", required: true, schema: new OA\Schema(type: "string"))],
        responses: [
            new OA\Response(
                response: 200, 
                description: "Success",
                content: new OA\JsonContent(ref: "#/components/schemas/ShippingResource")
            )
        ]
    )]
    public function show(string $uuid): JsonResponse
    {
        $shipping = $this->service->findByUuidOrFail($uuid);
        $shipping->load('order'); 
        return $this->successResponse(new ShippingResource($shipping));
    }

    #[OA\Put(
        path: "/api/admin/shippings/{uuid}",
        summary: "Cập nhật trạng thái",
        security: [['bearerAuth' => []]],
        tags: ["Shipping"],
        parameters: [new OA\Parameter(name: "uuid", in: "path", required: true, schema: new OA\Schema(type: "string"))],
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "status", type: "string", enum: ["shipped", "delivered", "returned", "cancelled"]),
                    new OA\Property(property: "tracking_number", type: "string"),
                ]
            )
        ),
        responses: [new OA\Response(response: 200, description: "Updated")]
    )]
    public function update(UpdateShippingRequest $request, string $uuid): JsonResponse
    {
        $shipping = $this->service->update($uuid, $request->validated());
        return $this->successResponse(new ShippingResource($shipping->refresh()));
    }

    #[OA\Delete(
        path: "/api/admin/shippings/{uuid}",
        summary: "Xóa vận đơn",
        security: [['bearerAuth' => []]],
        tags: ["Shipping"],
        parameters: [new OA\Parameter(name: "uuid", in: "path", required: true, schema: new OA\Schema(type: "string"))],
        responses: [new OA\Response(response: 200, description: "Deleted")]
    )]
    public function destroy(string $uuid): JsonResponse
    {
        $this->service->delete($uuid);
        return $this->successResponse(null, 'Deleted successfully');
    }
}