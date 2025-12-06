<?php

declare(strict_types=1);

namespace Modules\Order\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Modules\Shared\Http\Controllers\BaseController;
use Modules\Shared\Http\Resources\ApiResponse;
use Modules\Order\Services\OrderService;
use Modules\Order\Http\Requests\CreateOrderRequest;
use Modules\Order\Http\Requests\BuyNowRequest; 
use Modules\Order\Http\Requests\CheckoutRequest;
use Modules\Order\Domain\Models\Order;
use Modules\Order\Http\Resources\OrderResource;
use Illuminate\Validation\ValidationException;
use Exception;
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
            new OA\Parameter(name: "status", in: "query", schema: new OA\Schema(type: "string", enum: ["pending", "processing", "shipped", "delivered", "cancelled"])),
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

        if (!$user->hasPermissionTo('order.view_all')) { 
            $filters['user_id'] = $user->id;
        }

        $data = $this->service->paginate($request->integer('per_page', 15), $filters);
        return response()->json(ApiResponse::paginated($data));
    }

    #[OA\Post(
        path: "/orders/checkout",
        summary: "Đặt hàng từ giỏ hàng (Checkout)",
        security: [['bearerAuth' => []]],
        tags: ["Orders"],
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(
                required: ["address_id"],
                properties: [
                    new OA\Property(property: "address_id", type: "integer", description: "ID của địa chỉ nhận hàng"),
                    new OA\Property(property: "notes", type: "string", description: "Ghi chú đơn hàng", nullable: true),
                    // [ĐÃ SỬA]: Thêm trường cho các item được chọn
                    new OA\Property(
                        property: "selected_item_uuids", 
                        type: "array", 
                        items: new OA\Items(type: "string", format: "uuid"),
                        description: "Danh sách UUID của các Cart Item được chọn để checkout (Checkbox). Nếu không có, mặc định mua hết."
                    ),
                ]
            )
        ),
        responses: [ 
            new OA\Response(response: 201, description: "Order placed"),
            new OA\Response(response: 422, description: "Cart Empty / Out of Stock")
        ]
    )]
    public function checkout(CheckoutRequest $request): JsonResponse
    {
        try {
            $this->authorize('create', Order::class);

            $validated = $request->validated();

            $order = $this->service->createFromCart($validated);
            
            return response()->json(ApiResponse::success($order, 'Order placed successfully', 201), 201);

        } catch (ValidationException $e) {
            return response()->json(ApiResponse::error($e->getMessage(), 422, $e->errors()), 422);
        } catch (Exception $e) {
            return response()->json(ApiResponse::error('Checkout failed: ' . $e->getMessage(), 500), 500);
        }
    }

    #[OA\Post(
        path: "/orders/buy-now",
        summary: "Mua ngay (Bỏ qua giỏ hàng)",
        security: [['bearerAuth' => []]],
        tags: ["Orders"],
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(
                required: ["variant_uuid", "quantity", "address_id"],
                properties: [
                    new OA\Property(property: "variant_uuid", type: "string", format: "uuid"),
                    new OA\Property(property: "quantity", type: "integer", example: 1),
                    new OA\Property(property: "address_id", type: "integer"),
                    new OA\Property(property: "voucher_code", type: "string"),
                    new OA\Property(property: "notes", type: "string"),
                ]
            )
        ),
        responses: [ 
            new OA\Response(response: 201, description: "Order placed"),
            new OA\Response(response: 422, description: "Validation Error / Out of Stock")
        ]
    )]
    public function buyNow(BuyNowRequest $request): JsonResponse
    {
        try {

            $this->authorize('create', Order::class);
            $data = $request->validated();

            $data['user_id'] = $request->user()->id;

            $order = $this->service->createBuyNow($data);
            
            return response()->json(ApiResponse::success($order, 'Order placed successfully', 201), 201);

        } catch (ValidationException $e) {
            return response()->json(ApiResponse::error($e->getMessage(), 422, $e->errors()), 422);
        } catch (Exception $e) {
            return response()->json(ApiResponse::error('Buy Now failed: ' . $e->getMessage(), 500), 500);
        }
    }

    #[OA\Post(
        path: "/orders",
        summary: "Tạo đơn hàng thủ công (Admin)",
        security: [['bearerAuth' => []]],
        tags: ["Orders"],
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(
                required: ["address_id", "items"],
                properties: [
                    new OA\Property(property: "address_id", type: "integer"),
                    new OA\Property(property: "notes", type: "string"),
                    new OA\Property(property: "items", type: "array", items: new OA\Items(
                        properties: [
                            new OA\Property(property: "variant_uuid", type: "string", format: "uuid"),
                            new OA\Property(property: "quantity", type: "integer")
                        ]
                    )),
                ]
            )
        ),
        responses: [ 
            new OA\Response(response: 201, description: "Created"),
            new OA\Response(response: 422, description: "Validation Error / Out of stock")
        ]
    )]
    public function store(CreateOrderRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();
            if (!isset($data['user_id'])) {
                $data['user_id'] = $request->user()->id;
            }
            
            $order = $this->service->create($data);
            return response()->json(ApiResponse::success($order, 'Order created successfully', 201), 201);

        } catch (ValidationException $e) {
            return response()->json(ApiResponse::error($e->getMessage(), 422, $e->errors()), 422);
        } catch (Exception $e) {
            return response()->json(ApiResponse::error('Failed to create order: ' . $e->getMessage(), 500), 500);
        }
    }

    #[OA\Get(
        path: "/orders/{uuid}",
        summary: "Xem chi tiết đơn hàng",
        security: [['bearerAuth' => []]],
        tags: ["Orders"],
        parameters: [ new OA\Parameter(name: "uuid", in: "path", required: true, schema: new OA\Schema(type: "string")) ],
        responses: [ new OA\Response(response: 200, description: "Success") ]
    )]
    public function show(string $uuid): JsonResponse
    {
        $order = $this->service->findByUuidOrFail($uuid);
        $this->authorize('view', $order);

        $order->load(['items.variant.product.images', 'user']);
        
        return response()->json(ApiResponse::success(new OrderResource($order)));
    }

    #[OA\Put(
        path: "/orders/{uuid}/status",
        summary: "Cập nhật trạng thái (Admin)",
        security: [['bearerAuth' => []]],
        tags: ["Orders"],
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(
                required: ["status"],
                properties: [ new OA\Property(property: "status", type: "string", enum: ["processing", "shipped", "delivered", "cancelled"]) ]
            )
        ),
        responses: [ new OA\Response(response: 200, description: "Updated") ]
    )]
    public function updateStatus(Request $request, string $uuid): JsonResponse
    {
        try {
            $this->authorize('update', Order::class);

            $request->validate([
                'status' => 'required|string|in:pending,processing,shipped,delivered,cancelled'
            ]);

            $order = $this->service->updateStatus($uuid, $request->input('status'));
            
            return response()->json(ApiResponse::success($order, 'Order status updated'));

        } catch (Exception $e) {
            return response()->json(ApiResponse::error('Update status failed: ' . $e->getMessage(), 500), 500);
        }
    }

    #[OA\Post(
        path: "/orders/{uuid}/cancel",
        summary: "Hủy đơn hàng",
        security: [['bearerAuth' => []]],
        tags: ["Orders"],
        parameters: [ new OA\Parameter(name: "uuid", in: "path", required: true, schema: new OA\Schema(type: "string")) ],
        responses: [ new OA\Response(response: 200, description: "Cancelled") ]
    )]
    public function cancel(string $uuid): JsonResponse
    {
        try {
            $order = $this->service->findByUuidOrFail($uuid);
            $this->authorize('cancel', $order);

            $cancelledOrder = $this->service->cancel($uuid);
            
            return response()->json(ApiResponse::success($cancelledOrder, 'Order cancelled successfully'));

        } catch (ValidationException $e) {
            return response()->json(ApiResponse::error($e->getMessage(), 422), 422);
        } catch (Exception $e) {
            return response()->json(ApiResponse::error('Cancel failed: ' . $e->getMessage(), 500), 500);
        }
    }
}