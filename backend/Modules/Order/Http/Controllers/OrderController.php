<?php

namespace Modules\Order\Http\Controllers;

use Modules\Shared\Http\Controllers\BaseController;
use Modules\Shared\Http\Resources\ApiResponse;
use Modules\Order\Services\OrderService;
use Modules\Order\Http\Requests\CreateOrderRequest;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
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
        
        $order = $this->service->create($request->validated());
        return response()->json(ApiResponse::success($order, 'Order created successfully', 201), 201);
    }

    public function show(string $uuid): JsonResponse
    {
        $order = $this->service->findByUuidOrFail($uuid);
        
        if (!auth()->user()->hasRole('admin') && $order->user_id !== auth()->id()) {
            return response()->json(ApiResponse::error('Unauthorized', 403), 403);
        }

        $order->load(['items.product', 'items.warehouse']);
        return response()->json(ApiResponse::success($order));
    }

    public function cancel(string $uuid): JsonResponse
    {
        $order = $this->service->cancel($uuid);
        return response()->json(ApiResponse::success($order, 'Order cancelled successfully'));
    }
}