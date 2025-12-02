<?php

namespace Modules\Order\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Modules\Shared\Http\Controllers\BaseController;
use Modules\Shared\Http\Resources\ApiResponse;
use Modules\Order\Services\OrderService;
use Modules\Order\Http\Requests\CreateOrderRequest;
use OpenApi\Attributes as OA;

#[OA\Tag(
    name: "Orders",
    description: "API quản lý Đơn hàng (User xem của mình, Admin quản lý tất cả)"
)]

class OrderController extends BaseController
{
    public function __construct(OrderService $service)
    {
        parent::__construct($service);
    }
    
    #[OA\Get(
        path: "/api/orders",
        summary: "Xem danh sách đơn hàng (User chỉ xem của mình, Admin xem tất cả)",
        security: [['bearerAuth' => []]],
        tags: ["Orders"],
        parameters: [
            new OA\Parameter(name: "status", in: "query", description: "Filter theo trạng thái (pending, shipped, delivered, cancelled)", required: false, schema: new OA\Schema(type: "string")),
            new OA\Parameter(name: "page", in: "query", schema: new OA\Schema(type: "integer")),
            new OA\Parameter(name: "per_page", in: "query", schema: new OA\Schema(type: "integer")),
        ],
        responses: [
            new OA\Response(response: 200, description: "Successful operation")
        ]
    )]

    public function index(Request $request): JsonResponse
    {
        $filters = $request->all();
        
        if (!auth()->user()->hasRole('admin')) { 
            $filters['user_id'] = auth()->id();
        }

        $data = $this->service->paginate($request->get('per_page', 15), $filters);
        return response()->json(ApiResponse::paginated($data));
    }

     #[OA\Post(
        path: "/api/orders",
        summary: "Tạo đơn hàng mới (Chủ yếu dùng bởi Admin)",
        security: [['bearerAuth' => []]],
        tags: ["Orders"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["address_id", "items"],
                properties: [
                    new OA\Property(property: "address_id", type: "integer", description: "ID của địa chỉ giao hàng"),
                    new OA\Property(property: "notes", type: "string", nullable: true),
                    new OA\Property(property: "items", type: "array", items: new OA\Items(
                        required: ["product_uuid", "quantity"],
                        properties: [
                            new OA\Property(property: "product_uuid", type: "string"),
                            new OA\Property(property: "quantity", type: "integer", minimum: 1),
                        ]
                    )),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: "Order created successfully"),
            new OA\Response(response: 422, description: "Validation error (stock issue, invalid address)"),
        ]
    )]

    public function store(CreateOrderRequest $request): JsonResponse
    {
        $order = $this->service->create($request->validated());
        return response()->json(ApiResponse::success($order, 'Order created successfully', 201), 201);
    }
    
    #[OA\Post(
        path: "/api/orders/checkout",
        summary: "Đặt hàng từ giỏ hàng",
        security: [['bearerAuth' => []]],
        tags: ["Orders"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["address_id"],
                properties: [
                    new OA\Property(property: "address_id", type: "integer", description: "ID của địa chỉ giao hàng"),
                    new OA\Property(property: "notes", type: "string", nullable: true),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: "Order placed successfully"),
            new OA\Response(response: 422, description: "Validation error (Empty cart, stock issue, invalid address)"),
        ]
    )]

    public function checkout(Request $request): JsonResponse
    {
        $request->validate([
            'address_id' => 'required|exists:addresses,id',
            'notes' => 'nullable|string'
        ]);

        $order = $this->service->createFromCart($request->all());
        
        return response()->json(ApiResponse::success($order, 'Order placed successfully from cart', 201), 201);
    }

    #[OA\Get(
        path: "/api/orders/{uuid}",
        summary: "Xem chi tiết đơn hàng",
        security: [['bearerAuth' => []]],
        tags: ["Orders"],
        parameters: [
            new OA\Parameter(name: "uuid", in: "path", required: true, schema: new OA\Schema(type: "string", format: "uuid"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Successful operation"),
            new OA\Response(response: 403, description: "Forbidden (Not owner or Admin)"),
            new OA\Response(response: 404, description: "Order not found")
        ]
    )]

    public function show(string $uuid): JsonResponse
    {
        $order = $this->service->findByUuidOrFail($uuid);
        
        $this->authorize('view', $order);

        $order->load(['items.product', 'items.warehouse', 'shipping']);
        return response()->json(ApiResponse::success($order));
    }

    #[OA\Post(
        path: "/api/orders/{uuid}/cancel",
        summary: "Hủy đơn hàng",
        security: [['bearerAuth' => []]],
        tags: ["Orders"],
        parameters: [
            new OA\Parameter(name: "uuid", in: "path", required: true, schema: new OA\Schema(type: "string", format: "uuid"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Order cancelled successfully"),
            new OA\Response(response: 403, description: "Forbidden (Not owner or Admin)"),
            new OA\Response(response: 422, description: "Cannot cancel shipped/delivered order"),
            new OA\Response(response: 404, description: "Order not found")
        ]
    )]
    
    public function cancel(string $uuid): JsonResponse
    {
        $order = $this->service->getRepository()->findByUuid($uuid);
        
        if (!$order) {
            return response()->json(ApiResponse::error('Order not found', 404), 404);
        }

        $this->authorize('cancel', $order);

        $cancelledOrder = $this->service->cancel($uuid);
        
        return response()->json(ApiResponse::success($cancelledOrder, 'Order cancelled successfully'));
    }
}