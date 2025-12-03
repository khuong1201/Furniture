<?php

namespace Modules\Order\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Modules\Shared\Http\Controllers\BaseController;
use Modules\Shared\Http\Resources\ApiResponse;
use Modules\Order\Services\OrderService;
use Modules\Order\Http\Requests\CreateOrderRequest;
use Modules\Order\Domain\Models\Order;
use OpenApi\Attributes as OA;

#[OA\Tag(name: "Orders", description: "API quản lý Đơn hàng")]
class OrderController extends BaseController
{
    public function __construct(OrderService $service)
    {
        parent::__construct($service);
    }
    
    #[OA\Get(
        path: "/orders",
        summary: "Xem danh sách đơn hàng",
        security: [['bearerAuth' => []]],
        tags: ["Orders"],
        parameters: [
            new OA\Parameter(name: "status", in: "query", schema: new OA\Schema(type: "string")),
            new OA\Parameter(name: "page", in: "query", schema: new OA\Schema(type: "integer")),
            new OA\Parameter(name: "per_page", in: "query", schema: new OA\Schema(type: "integer")),
        ],
        responses: [ new OA\Response(response: 200, description: "Success") ]
    )]
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Order::class);

        $filters = $request->all();
        $user = $request->user();

        if (!$user->hasPermissionTo('order.view')) { 
            $filters['user_id'] = $user->id;
        }

        $data = $this->service->paginate($request->get('per_page', 15), $filters);
        return response()->json(ApiResponse::paginated($data));
    }

    #[OA\Post(
        path: "/orders",
        summary: "Tạo đơn hàng mới (Admin/Staff)",
        security: [['bearerAuth' => []]],
        tags: ["Orders"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["address_id", "items"],
                properties: [
                    new OA\Property(property: "address_id", type: "integer"),
                    new OA\Property(property: "items", type: "array", items: new OA\Items(
                        properties: [
                            new OA\Property(property: "variant_uuid", type: "string", format: "uuid"),
                            new OA\Property(property: "quantity", type: "integer")
                        ]
                    )),
                ]
            )
        ),
        responses: [ new OA\Response(response: 201, description: "Created") ]
    )]
    public function store(CreateOrderRequest $request): JsonResponse
    {
        $data = $request->validated();

        if (!isset($data['user_id'])) {
            $data['user_id'] = $request->user()->id;
        }
        
        $order = $this->service->create($data);
        return response()->json(ApiResponse::success($order, 'Order created successfully', 201), 201);
    }
    
    #[OA\Post(
        path: "/orders/checkout",
        summary: "Đặt hàng từ giỏ hàng (User)",
        security: [['bearerAuth' => []]],
        tags: ["Orders"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["address_id"],
                properties: [
                    new OA\Property(property: "address_id", type: "integer"),
                    new OA\Property(property: "notes", type: "string"),
                ]
            )
        ),
        responses: [ new OA\Response(response: 201, description: "Order placed") ]
    )]
    public function checkout(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'address_id' => 'required|exists:addresses,id',
            'notes' => 'nullable|string'
        ]);

        $order = $this->service->createFromCart($validated);
        
        return response()->json(ApiResponse::success($order, 'Order placed successfully from cart', 201), 201);
    }

    #[OA\Get(
        path: "/orders/{uuid}",
        summary: "Xem chi tiết đơn hàng",
        security: [['bearerAuth' => []]],
        tags: ["Orders"],
        parameters: [
            new OA\Parameter(name: "uuid", in: "path", required: true, schema: new OA\Schema(type: "string", format: "uuid"))
        ],
        responses: [ new OA\Response(response: 200, description: "Success") ]
    )]
    public function show(string $uuid): JsonResponse
    {
        $order = $this->service->findByUuidOrFail($uuid);
        
        $this->authorize('view', $order);

        $order->load(['items.variant.product.images', 'items.variant.attributeValues.attribute']);
        
        return response()->json(ApiResponse::success($order));
    }

    #[OA\Post(
        path: "/orders/{uuid}/cancel",
        summary: "Hủy đơn hàng",
        security: [['bearerAuth' => []]],
        tags: ["Orders"],
        parameters: [
            new OA\Parameter(name: "uuid", in: "path", required: true, schema: new OA\Schema(type: "string", format: "uuid"))
        ],
        responses: [ new OA\Response(response: 200, description: "Cancelled") ]
    )]
    public function cancel(string $uuid): JsonResponse
    {
        $order = $this->service->findByUuidOrFail($uuid);

        $this->authorize('cancel', $order);

        $cancelledOrder = $this->service->cancel($uuid);
        
        return response()->json(ApiResponse::success($cancelledOrder, 'Order cancelled successfully'));
    }
}