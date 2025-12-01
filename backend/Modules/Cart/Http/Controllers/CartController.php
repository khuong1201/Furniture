<?php

namespace Modules\Cart\Http\Controllers;

use Illuminate\Http\Request;
use Modules\Shared\Http\Controllers\BaseController;
use Modules\Shared\Http\Resources\ApiResponse;
use Modules\Cart\Services\CartService;
use Modules\Cart\Http\Requests\AddToCartRequest;
use Illuminate\Http\JsonResponse;
use Modules\Cart\Http\Requests\UpdateCartItemRequest;

class CartController extends BaseController
{
    public function __construct(CartService $service)
    {
        parent::__construct($service);
    }

    public function index(Request $request): JsonResponse
    {
        $data = $this->service->getMyCart($request->user()->id);
        return response()->json(ApiResponse::success($data));
    }

    public function store(Request $request): JsonResponse
    {
        $validatedRequest = app(AddToCartRequest::class);
        $data = $this->service->addToCart($request->user()->id, $validatedRequest->validated());
        return response()->json(ApiResponse::success($data, 'Added to cart'));
    }

    public function update(Request $request, string $itemUuid): JsonResponse
    {
        $validatedRequest = app(UpdateCartItemRequest::class);
        $data = $this->service->updateItem($itemUuid, $request->input('quantity'), $request->user()->id);
        return response()->json(ApiResponse::success($data, 'Cart updated'));
    }

    public function destroy(string $uuid): JsonResponse
    {
        $userId = request()->user()->id;
        $data = $this->service->removeItem($uuid, $userId);
        return response()->json(ApiResponse::success($data, 'Item removed'));
    }
    
    public function clear(Request $request): JsonResponse
    {
        $this->service->clearCart($request->user()->id);
        return response()->json(ApiResponse::success(null, 'Cart cleared'));
    }
}