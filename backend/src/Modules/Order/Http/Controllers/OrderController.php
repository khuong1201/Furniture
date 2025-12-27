<?php

declare(strict_types=1);

namespace Modules\Order\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Order\Domain\Models\Order;
use Modules\Order\Http\Requests\BuyNowRequest;
use Modules\Order\Http\Requests\CheckoutRequest;
use Modules\Order\Http\Requests\CreateOrderRequest;
use Modules\Order\Http\Requests\UpdateOrderStatusRequest;
use Modules\Order\Http\Resources\OrderResource;
use Modules\Order\Services\OrderService;
use Modules\Shared\Http\Controllers\BaseController;
use OpenApi\Attributes as OA;

#[OA\Tag(name: "Orders", description: "API quản lý Đơn hàng & Thanh toán")]
class OrderController extends BaseController
{
    public function __construct(protected OrderService $orderService)
    {
        parent::__construct($orderService);
    }

    #[OA\Get(path: "/api/orders", summary: "Danh sách đơn hàng", tags: ["Orders"])]
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Order::class);
        
        $user = $request->user();
        $filters = $request->all();

        $isAdmin = $user->hasRole('super-admin') 
                || $user->hasRole('admin') 
                || $user->hasPermissionTo('order.view_all');

        if (!$isAdmin) {
            $filters['user_id'] = $user->id;
        }

        $perPage = $request->integer('per_page', 15);
        $filters['per_page'] = $perPage;

        $paginator = $this->orderService->filter($perPage, $filters);

        $paginator->through(fn($order) => new OrderResource($order));

        return $this->successResponse($paginator);
    }

    #[OA\Get(path: "/api/orders/{uuid}", summary: "Chi tiết đơn hàng", tags: ["Orders"])]
    public function show(string $uuid): JsonResponse
    {
        $order = $this->orderService->findByUuidOrFail($uuid);
        $this->authorize('view', $order);
        
        // Eager load quan trọng
        $order->load(['items', 'user', 'shipping']);
        
        return $this->successResponse(new OrderResource($order));
    }

    #[OA\Post(
        path: "/api/orders/checkout", 
        summary: "Checkout từ giỏ hàng", 
        tags: ["Orders"],
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(properties: [
                new OA\Property(property: "address_id", type: "integer"),
                new OA\Property(property: "consignee_name", type: "string", description: "Tên người nhận (Optional)"),
                new OA\Property(property: "consignee_phone", type: "string", description: "SĐT người nhận (Optional)"),
                new OA\Property(property: "notes", type: "string"),
                new OA\Property(property: "voucher_code", type: "string"),
                new OA\Property(property: "selected_item_uuids", type: "array", items: new OA\Items(type: "string"))
            ])
        )
    )]
    public function checkout(CheckoutRequest $request): JsonResponse
    {
        $this->authorize('create', Order::class); 

        $order = $this->orderService->createFromCart($request->validated());
        
        return $this->successResponse(new OrderResource($order), 'Đặt hàng thành công', 201);
    }

    #[OA\Post(
        path: "/api/orders/buy-now", 
        summary: "Mua ngay (1 sản phẩm)", 
        tags: ["Orders"],
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(properties: [
                new OA\Property(property: "variant_uuid", type: "string"),
                new OA\Property(property: "quantity", type: "integer"),
                new OA\Property(property: "address_id", type: "integer"),
                new OA\Property(property: "consignee_name", type: "string"),
                new OA\Property(property: "consignee_phone", type: "string"),
                new OA\Property(property: "notes", type: "string"),
                new OA\Property(property: "voucher_code", type: "string"),
            ])
        )
    )]
    public function buyNow(BuyNowRequest $request): JsonResponse
    {
        $this->authorize('create', Order::class);
        
        $data = $request->validated();
        $data['user_id'] = $request->user()->id;

        $order = $this->orderService->createBuyNow($data);
        
        return $this->successResponse(new OrderResource($order), 'Đặt hàng thành công', 201);
    }

    #[OA\Post(path: "/api/orders/{uuid}/cancel", summary: "Hủy đơn hàng", tags: ["Orders"])]
    public function cancel(string $uuid): JsonResponse
    {
        $order = $this->orderService->findByUuidOrFail($uuid);
        $this->authorize('cancel', $order);

        $cancelledOrder = $this->orderService->cancel($uuid);
        
        return $this->successResponse(new OrderResource($cancelledOrder), 'Đã hủy đơn hàng');
    }

    #[OA\Put(
        path: "/api/admin/orders/{uuid}/status",
        summary: "Cập nhật trạng thái (Admin)",
        tags: ["Orders"],
        requestBody: new OA\RequestBody(content: new OA\JsonContent(properties: [
            new OA\Property(property: "status", type: "string", enum: ["processing", "shipping", "delivered", "cancelled"])
        ]))
    )]
    public function updateStatus(UpdateOrderStatusRequest $request, string $uuid): JsonResponse
    {
        $order = $this->orderService->findByUuidOrFail($uuid);
        $this->authorize('update', $order);

        $updatedOrder = $this->orderService->updateStatus($uuid, $request->input('status'));

        return $this->successResponse(new OrderResource($updatedOrder), 'Cập nhật trạng thái thành công');
    }

    #[OA\Post(
        path: "/api/admin/orders/create",
        summary: "Tạo đơn thủ công (Admin)",
        tags: ["Orders"]
    )]
    public function store(CreateOrderRequest $request): JsonResponse 
    {
        $this->authorize('create', Order::class);
        
        $order = $this->orderService->create($request->validated());

        return $this->successResponse(new OrderResource($order), 'Tạo đơn hàng thành công', 201);
    }
}