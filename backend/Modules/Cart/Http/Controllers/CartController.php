<?php

declare(strict_types=1);

namespace Modules\Cart\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Modules\Shared\Http\Controllers\BaseController;
use Modules\Shared\Http\Resources\ApiResponse;
use Modules\Cart\Services\CartService;
use Modules\Cart\Http\Requests\AddToCartRequest;
use Modules\Cart\Http\Requests\UpdateCartItemRequest;
use OpenApi\Attributes as OA;

#[OA\Tag(name: "Cart", description: "API quản lý Giỏ hàng")]
class CartController extends BaseController
{
    public function __construct(CartService $service)
    {
        parent::__construct($service);
    }

    #[OA\Get(
        path: "/carts",
        summary: "Xem giỏ hàng của tôi",
        security: [['bearerAuth' => []]],
        tags: ["Cart"],
        responses: [ 
            new OA\Response(
                response: 200, 
                description: "Success",
                content: new OA\JsonContent(properties: [
                    new OA\Property(property: "success", type: "boolean", example: true),
                    new OA\Property(property: "data", type: "object")
                ])
            ) 
        ]
    )]
    public function index(Request $request): JsonResponse
    {
        $data = $this->service->getMyCart($request->user()->id);
        return response()->json(ApiResponse::success($data));
    }

    #[OA\Post(
        path: "/carts",
        summary: "Thêm sản phẩm vào giỏ",
        security: [['bearerAuth' => []]],
        tags: ["Cart"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["variant_uuid", "quantity"],
                properties: [
                    new OA\Property(property: "variant_uuid", type: "string", format: "uuid"),
                    new OA\Property(property: "quantity", type: "integer", minimum: 1),
                ]
            )
        ),
        responses: [ new OA\Response(response: 200, description: "Added") ]
    )]
    public function store(AddToCartRequest $request): JsonResponse
    {
        $data = $this->service->addToCart($request->user()->id, $request->validated());
        return response()->json(ApiResponse::success($data, 'Added to cart'));
    }

    #[OA\Put(
        path: "/carts/{itemUuid}",
        summary: "Cập nhật số lượng item",
        security: [['bearerAuth' => []]],
        tags: ["Cart"],
        parameters: [
            new OA\Parameter(name: "itemUuid", in: "path", required: true, schema: new OA\Schema(type: "string", format: "uuid"))
        ],
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "quantity", type: "integer", minimum: 0),
                ]
            )
        ),
        responses: [ new OA\Response(response: 200, description: "Updated") ]
    )]
    public function update(UpdateCartItemRequest $request, string $itemUuid): JsonResponse
    {
        $cartItem = $this->service->findCartItemOrFail($itemUuid);
        $this->authorize('update', $cartItem);

        $data = $this->service->updateItem($cartItem, (int)$request->input('quantity'), $request->user()->id);
        
        return response()->json(ApiResponse::success($data, 'Cart updated'));
    }

    #[OA\Delete(
        path: "/carts/{uuid}",
        summary: "Xóa 1 item",
        security: [['bearerAuth' => []]],
        tags: ["Cart"],
        parameters: [
            new OA\Parameter(name: "uuid", in: "path", required: true, schema: new OA\Schema(type: "string", format: "uuid"))
        ],
        responses: [ new OA\Response(response: 200, description: "Deleted") ]
    )]
    public function destroy(string $uuid): JsonResponse
    {
        $cartItem = $this->service->findCartItemOrFail($uuid);
        $this->authorize('delete', $cartItem);
        
        $data = $this->service->removeItem($cartItem, request()->user()->id);
        
        return response()->json(ApiResponse::success($data, 'Item removed'));
    }

    #[OA\Post(
        path: "/carts/bulk-delete",
        summary: "Xóa nhiều sản phẩm đã chọn (Checkbox)",
        security: [['bearerAuth' => []]],
        tags: ["Cart"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["uuids"],
                properties: [
                    new OA\Property(
                        property: "uuids", 
                        type: "array", 
                        items: new OA\Items(type: "string", format: "uuid"),
                        description: "Danh sách UUID của các item trong giỏ hàng cần xóa"
                    )
                ]
            )
        ),
        responses: [ 
            new OA\Response(
                response: 200, 
                description: "Selected items deleted",
                content: new OA\JsonContent(properties: [
                    new OA\Property(property: "success", type: "boolean", example: true),
                    new OA\Property(property: "data", type: "object")
                ])
            ) 
        ]
    )]
    public function bulkDestroy(Request $request): JsonResponse
    {
        // Validate đầu vào
        $request->validate([
            'uuids' => 'required|array',
            'uuids.*' => 'required|uuid'
        ]);

        // Gọi service để xóa list item
        // Lưu ý: Đảm bảo bạn đã thêm hàm removeItemsList vào CartService như hướng dẫn trước
        $this->service->removeItemsList($request->user()->id, $request->input('uuids'));

        // Trả về data giỏ hàng mới nhất để Frontend cập nhật UI
        $data = $this->service->getMyCart($request->user()->id);
        
        return response()->json(ApiResponse::success($data, 'Selected items removed'));
    }
    
    #[OA\Delete(
        path: "/carts",
        summary: "Làm trống giỏ hàng (Xóa tất cả)",
        security: [['bearerAuth' => []]],
        tags: ["Cart"],
        responses: [ new OA\Response(response: 200, description: "Cleared") ]
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