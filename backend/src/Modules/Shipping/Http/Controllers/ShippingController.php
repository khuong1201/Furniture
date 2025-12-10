<?php

declare(strict_types=1);

namespace Modules\Shipping\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Modules\Shared\Http\Controllers\BaseController;
use Modules\Shared\Http\Resources\ApiResponse;
use Modules\Shipping\Services\ShippingService;
use Modules\Shipping\Domain\Models\Shipping;
use Modules\Shipping\Http\Requests\StoreShippingRequest;
use Modules\Shipping\Http\Requests\UpdateShippingRequest;
use OpenApi\Attributes as OA;

#[OA\Tag(name: "Shipping", description: "API quản lý Vận chuyển (Admin/Shipper)")]
class ShippingController extends BaseController
{
    public function __construct(ShippingService $service)
    {
        parent::__construct($service);
    }

    #[OA\Get(
        path: "/admin/shippings",
        summary: "Xem danh sách vận đơn",
        security: [['bearerAuth' => []]],
        tags: ["Shipping"],
        parameters: [
            new OA\Parameter(name: "tracking_number", in: "query", schema: new OA\Schema(type: "string")),
            new OA\Parameter(name: "order_uuid", in: "query", schema: new OA\Schema(type: "string", format: "uuid")),
            new OA\Parameter(name: "status", in: "query", schema: new OA\Schema(type: "string", enum: ["pending", "shipped", "delivered", "returned"])),
            new OA\Parameter(name: "page", in: "query", schema: new OA\Schema(type: "integer")),
            new OA\Parameter(name: "per_page", in: "query", schema: new OA\Schema(type: "integer")),
        ],
        responses: [ 
            new OA\Response(response: 200, description: "Success"),
            new OA\Response(response: 403, description: "Forbidden")
        ]
    )]
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Shipping::class);

        $filters = $request->all();

        $data = $this->service->paginate($request->integer('per_page', 15), $filters);
        return response()->json(ApiResponse::paginated($data));
    }

    #[OA\Post(
        path: "/admin/shippings",
        summary: "Tạo vận đơn mới (Ship hàng)",
        security: [['bearerAuth' => []]],
        tags: ["Shipping"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["order_uuid", "provider", "tracking_number"],
                properties: [
                    new OA\Property(property: "order_uuid", type: "string", format: "uuid"),
                    new OA\Property(property: "provider", type: "string", example: "GHTK"),
                    new OA\Property(property: "tracking_number", type: "string", example: "S123456789"),
                    new OA\Property(property: "fee", type: "number", nullable: true, description: "Phí ship thực tế (nếu có)"),
                ]
            )
        ),
        responses: [ new OA\Response(response: 201, description: "Created") ]
    )]
    public function store(StoreShippingRequest $request): JsonResponse
    {
        $this->authorize('create', Shipping::class);
        
        $shipping = $this->service->create($request->validated());
        
        return response()->json(ApiResponse::success($shipping, 'Shipping created', 201), 201);
    }

    #[OA\Get(
        path: "/admin/shippings/{uuid}",
        summary: "Xem chi tiết vận đơn",
        security: [['bearerAuth' => []]],
        tags: ["Shipping"],
        parameters: [new OA\Parameter(name: "uuid", in: "path", required: true, schema: new OA\Schema(type: "string", format: "uuid"))],
        responses: [ new OA\Response(response: 200, description: "Success") ]
    )]
    public function show(string $uuid): JsonResponse
    {
        $shipping = $this->service->findByUuidOrFail($uuid);

        $this->authorize('view', $shipping);

        $shipping->load('order.user'); 

        return response()->json(ApiResponse::success($shipping));
    }

    #[OA\Put(
        path: "/admin/shippings/{uuid}",
        summary: "Cập nhật trạng thái vận đơn",
        security: [['bearerAuth' => []]],
        tags: ["Shipping"],
        parameters: [new OA\Parameter(name: "uuid", in: "path", required: true, schema: new OA\Schema(type: "string", format: "uuid"))],
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "status", type: "string", enum: ["shipped", "delivered", "returned", "cancelled"]),
                    new OA\Property(property: "tracking_number", type: "string"),
                ]
            )
        ),
        responses: [ new OA\Response(response: 200, description: "Updated") ]
    )]
    public function update(UpdateShippingRequest $request, string $uuid): JsonResponse
    {
        $shipping = $this->service->findByUuidOrFail($uuid);

        $this->authorize('update', $shipping);
        
        $shipping = $this->service->update($uuid, $request->validated());
        
        return response()->json(ApiResponse::success($shipping, 'Shipping updated'));
    }

    #[OA\Delete(
        path: "/admin/shippings/{uuid}",
        summary: "Xóa vận đơn",
        security: [['bearerAuth' => []]],
        tags: ["Shipping"],
        parameters: [new OA\Parameter(name: "uuid", in: "path", required: true, schema: new OA\Schema(type: "string", format: "uuid"))],
        responses: [ new OA\Response(response: 200, description: "Deleted") ]
    )]
    public function destroy(string $uuid): JsonResponse
    {
        $shipping = $this->service->findByUuidOrFail($uuid);

        $this->authorize('delete', $shipping);

        $this->service->delete($uuid);

        return response()->json(ApiResponse::success(null, 'Shipping deleted'));
    }
}