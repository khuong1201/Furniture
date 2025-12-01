<?php

namespace Modules\Order\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Modules\Shared\Http\Controllers\BaseController;
use Modules\Shared\Http\Resources\ApiResponse;
use Modules\Order\Services\OrderService;
use Modules\Order\Http\Requests\CreateOrderRequest;

class OrderController extends BaseController
{
    public function __construct(OrderService $service)
    {
        parent::__construct($service);
    }

    public function index(Request $request): JsonResponse
    {
        $filters = $request->all();
        if (!auth()->user()->hasRole('admin')) { 
            $filters['user_id'] = auth()->id();
        }

        $data = $this->service->paginate($request->get('per_page', 15), $filters);
        return response()->json(ApiResponse::paginated($data));
    }

    public function store(Request $request): JsonResponse
    {
        $validatedRequest = app(CreateOrderRequest::class);
        $order = $this->service->create($validatedRequest->validated());
        return response()->json(ApiResponse::success($order, 'Order created successfully', 201), 201);
    }

    public function checkout(Request $request): JsonResponse
    {
        $request->validate([
            'address_id' => 'required|exists:addresses,id',
            'notes' => 'nullable|string'
        ]);

        $order = $this->service->createFromCart($request->all());
        
        return response()->json(ApiResponse::success($order, 'Order placed successfully from cart', 201), 201);
    }

    public function show(string $uuid): JsonResponse
    {
        $order = $this->service->findByUuidOrFail($uuid);
        
        if (auth()->check() && !auth()->user()->hasRole('admin') && $order->user_id !== auth()->id()) {
            return response()->json(ApiResponse::error('Unauthorized', 403), 403);
        }

        $order->load(['items.product', 'items.warehouse', 'shipping']);
        return response()->json(ApiResponse::success($order));
    }

    public function cancel(string $uuid): JsonResponse
    {
        $order = $this->service->cancel($uuid);
        return response()->json(ApiResponse::success($order, 'Order cancelled successfully'));
    }
}