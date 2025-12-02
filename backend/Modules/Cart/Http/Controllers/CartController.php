<?php

namespace Modules\Cart\Http\Controllers;

use Illuminate\Http\Request;
use Modules\Shared\Http\Controllers\BaseController;
use Modules\Shared\Http\Resources\ApiResponse;
use Modules\Cart\Services\CartService;
use Modules\Cart\Http\Requests\AddToCartRequest;
use Illuminate\Http\JsonResponse;
use Modules\Cart\Http\Requests\UpdateCartItemRequest;
use Modules\Cart\Domain\Models\CartItem;
use OpenApi\Attributes as OA;

#[OA\Tag(
    name: "Cart",
    description: "API quản lý Giỏ hàng (Chỉ User đang đăng nhập)"
)]

class CartController extends BaseController
{
    public function __construct(CartService $service)
    {
        parent::__construct($service);
    }

    #[OA\Get(
        path: "/api/carts",
        summary: "Xem giỏ hàng của tôi",
        security: [['bearerAuth' => []]],
        tags: ["Cart"],
        responses: [
            new OA\Response(response: 200, description: "Successful operation", content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "uuid", type: "string"),
                    new OA\Property(property: "items", type: "array", items: new OA\Items()),
                    new OA\Property(property: "total_amount", type: "number"),
                    new OA\Property(property: "item_count", type: "integer"),
                ]
            )),
            new OA\Response(response: 401, description: "Unauthenticated")
        ]
    )]

    public function index(Request $request): JsonResponse
    {
        $data = $this->service->getMyCart($request->user()->id);
        return response()->json(ApiResponse::success($data));
    }

    #[OA\Post(
        path: "/api/carts",
        summary: "Thêm sản phẩm vào giỏ hàng",
        security: [['bearerAuth' => []]],
        tags: ["Cart"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["product_uuid", "quantity"],
                properties: [
                    new OA\Property(property: "product_uuid", type: "string", format: "uuid"),
                    new OA\Property(property: "quantity", type: "integer", minimum: 1),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: "Added to cart"),
            new OA\Response(response: 422, description: "Validation error (stock or product not found)"),
        ]
    )]

    public function store(AddToCartRequest $request): JsonResponse
    {
        $data = $this->service->addToCart($request->user()->id, $request->validated());
        return response()->json(ApiResponse::success($data, 'Added to cart'));
    }

    #[OA\Put(
        path: "/api/carts/{itemUuid}",
        summary: "Cập nhật số lượng sản phẩm trong giỏ",
        security: [['bearerAuth' => []]],
        tags: ["Cart"],
        parameters: [
            new OA\Parameter(name: "itemUuid", in: "path", required: true, schema: new OA\Schema(type: "string", format: "uuid"))
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["quantity"],
                properties: [
                    new OA\Property(property: "quantity", type: "integer", minimum: 0, description: "Set quantity to 0 to remove item"),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: "Cart updated"),
            new OA\Response(response: 403, description: "Forbidden (Not owner)"),
            new OA\Response(response: 404, description: "Cart item not found")
        ]
    )]

    public function update(UpdateCartItemRequest $request, string $itemUuid): JsonResponse
    {
        $cartItem = CartItem::where('uuid', $itemUuid)->first();

        if (!$cartItem) {
            return response()->json(ApiResponse::error('Cart item not found', 404), 404);
        }

        $this->authorize('update', $cartItem);

        $data = $this->service->updateItem($cartItem, $request->input('quantity'), $request->user()->id);
        
        return response()->json(ApiResponse::success($data, 'Cart updated'));
    }

    #[OA\Delete(
        path: "/api/carts/{uuid}",
        summary: "Xóa một sản phẩm khỏi giỏ hàng",
        security: [['bearerAuth' => []]],
        tags: ["Cart"],
        parameters: [
            new OA\Parameter(name: "uuid", in: "path", description: "UUID của Cart Item", required: true, schema: new OA\Schema(type: "string", format: "uuid"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Item removed"),
            new OA\Response(response: 403, description: "Forbidden (Not owner)"),
            new OA\Response(response: 404, description: "Cart item not found")
        ]
    )]

    public function destroy(string $uuid): JsonResponse
    {
        $cartItem = CartItem::where('uuid', $uuid)->first();

        if (!$cartItem) {
            return response()->json(ApiResponse::error('Cart item not found', 404), 404);
        }

        $this->authorize('delete', $cartItem);
        
        $data = $this->service->removeItem($cartItem, request()->user()->id);
        
        return response()->json(ApiResponse::success($data, 'Item removed'));
    }
    
    #[OA\Delete(
        path: "/api/carts",
        summary: "Làm trống toàn bộ giỏ hàng",
        security: [['bearerAuth' => []]],
        tags: ["Cart"],
        responses: [
            new OA\Response(response: 200, description: "Cart cleared"),
            new OA\Response(response: 403, description: "Forbidden (Not owner)")
        ]
    )]
    
    public function clear(Request $request): JsonResponse
    {
        $cart = $this->service->getRepository()->findByUser($request->user()->id);

        if ($cart) {
            $this->authorize('clear', $cart);

            $this->service->clearCart($cart);
        }
        
        return response()->json(ApiResponse::success(null, 'Cart cleared'));
    }
}