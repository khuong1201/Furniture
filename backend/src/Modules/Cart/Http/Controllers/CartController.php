<?php

declare(strict_types=1);

namespace Modules\Cart\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Cart\Http\Requests\AddToCartRequest;
use Modules\Cart\Http\Requests\UpdateCartItemRequest;
use Modules\Cart\Services\CartService;
use Modules\Shared\Http\Controllers\BaseController;
use Modules\Shared\Http\Traits\ApiResponseTrait;
use OpenApi\Attributes as OA;

#[OA\Tag(
    name: "Cart", 
    description: "API Giỏ hàng & Mua sắm (Yêu cầu Token)"
)]
class CartController extends BaseController
{
    public function __construct(protected CartService $cartService)
    {
        parent::__construct($cartService);
    }

    #[OA\Get(
        path: "/carts",
        summary: "Xem giỏ hàng hiện tại",
        description: "Lấy thông tin chi tiết giỏ hàng của user đang đăng nhập, bao gồm các item, tổng tiền, và voucher đang áp dụng.",
        security: [['bearerAuth' => []]],
        tags: ["Cart"],
        responses: [
            new OA\Response(
                response: 200, 
                description: "Lấy dữ liệu thành công",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(
                            property: "data", 
                            type: "object",
                            properties: [
                                new OA\Property(property: "cart_uuid", type: "string", format: "uuid"),
                                new OA\Property(
                                    property: "items", 
                                    type: "array", 
                                    items: new OA\Items(
                                        properties: [
                                            new OA\Property(property: "uuid", type: "string", format: "uuid"),
                                            new OA\Property(property: "product_name", type: "string", example: "iPhone 15 Pro"),
                                            new OA\Property(property: "sku", type: "string", example: "IP15-BLK-256"),
                                            new OA\Property(property: "image", type: "string", format: "url"),
                                            new OA\Property(property: "options", type: "string", example: "Màu: Đen, Dung lượng: 256GB"),
                                            new OA\Property(property: "quantity", type: "integer", example: 1),
                                            new OA\Property(property: "stock_available", type: "integer", example: 10),
                                            new OA\Property(property: "is_stock_sufficient", type: "boolean", example: true),
                                            new OA\Property(
                                                property: "price", 
                                                type: "object", 
                                                properties: [
                                                    new OA\Property(property: "raw", type: "number", example: 25000000),
                                                    new OA\Property(property: "formatted", type: "string", example: "25.000.000 ₫")
                                                ]
                                            ),
                                            new OA\Property(
                                                property: "subtotal", 
                                                type: "object", 
                                                properties: [
                                                    new OA\Property(property: "raw", type: "number", example: 25000000),
                                                    new OA\Property(property: "formatted", type: "string", example: "25.000.000 ₫")
                                                ]
                                            )
                                        ]
                                    )
                                ),
                                new OA\Property(
                                    property: "summary", 
                                    type: "object",
                                    properties: [
                                        new OA\Property(property: "item_count", type: "integer", example: 3),
                                        new OA\Property(property: "currency", type: "string", example: "VND"),
                                        new OA\Property(
                                            property: "discount", 
                                            type: "object", 
                                            properties: [
                                                new OA\Property(property: "code", type: "string", nullable: true, example: "SALE50"),
                                                new OA\Property(property: "amount", type: "number", example: 50000),
                                                new OA\Property(property: "formatted", type: "string", example: "50.000 ₫")
                                            ]
                                        ),
                                        new OA\Property(
                                            property: "total", 
                                            type: "object", 
                                            properties: [
                                                new OA\Property(property: "raw", type: "number", example: 24950000),
                                                new OA\Property(property: "formatted", type: "string", example: "24.950.000 ₫")
                                            ]
                                        )
                                    ]
                                )
                            ]
                        )
                    ]
                )
            )
        ]
    )]
    public function index(Request $request): JsonResponse
    {
        $data = $this->cartService->getMyCart($request->user()->id);
        return $this->successResponse($data);
    }

    #[OA\Post(
        path: "/carts",
        summary: "Thêm sản phẩm vào giỏ",
        description: "Nếu sản phẩm đã có, sẽ cộng dồn số lượng. Sẽ báo lỗi nếu tồn kho không đủ.",
        security: [['bearerAuth' => []]],
        tags: ["Cart"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["variant_uuid", "quantity"],
                properties: [
                    new OA\Property(property: "variant_uuid", type: "string", format: "uuid", description: "UUID của biến thể sản phẩm"),
                    new OA\Property(property: "quantity", type: "integer", minimum: 1, example: 1)
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200, 
                description: "Thêm thành công (Trả về Cart mới nhất)",
                content: new OA\JsonContent(properties: [
                    new OA\Property(property: "success", type: "boolean", example: true),
                    new OA\Property(property: "message", type: "string", example: "Thêm vào giỏ hàng thành công"),
                    new OA\Property(property: "data", type: "object", description: "Cart Object (như API Index)")
                ])
            ),
            new OA\Response(
                response: 409, 
                description: "Hết hàng / Không đủ tồn kho",
                content: new OA\JsonContent(properties: [
                    new OA\Property(property: "success", type: "boolean", example: false),
                    new OA\Property(property: "error_code", type: "integer", example: 409042, description: "Mã lỗi: Out of stock"),
                    new OA\Property(property: "message", type: "string", example: "Sản phẩm chỉ còn 5 items.")
                ])
            ),
            new OA\Response(
                response: 422, 
                description: "Sản phẩm không tồn tại hoặc ngưng kinh doanh (422043)"
            )
        ]
    )]
    public function store(AddToCartRequest $request): JsonResponse
    {
        $data = $this->cartService->addToCart($request->user()->id, $request->validated());
        return $this->successResponse($data, 'Thêm vào giỏ hàng thành công');
    }

    #[OA\Put(
        path: "/carts/{itemUuid}",
        summary: "Cập nhật số lượng item",
        security: [['bearerAuth' => []]],
        tags: ["Cart"],
        parameters: [
            new OA\Parameter(
                name: "itemUuid", 
                in: "path", 
                required: true, 
                description: "UUID của Cart Item (Lấy từ API danh sách giỏ hàng)",
                schema: new OA\Schema(type: "string", format: "uuid")
            )
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["quantity"],
                properties: [
                    new OA\Property(property: "quantity", type: "integer", minimum: 0, example: 2, description: "Số lượng mới (0 để xóa)")
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200, 
                description: "Cập nhật thành công",
                content: new OA\JsonContent(properties: [
                    new OA\Property(property: "success", type: "boolean", example: true),
                    new OA\Property(property: "data", type: "object", description: "Cart Object updated")
                ])
            ),
            new OA\Response(
                response: 409, 
                description: "Số lượng vượt quá tồn kho (409042)"
            ),
            new OA\Response(
                response: 404, 
                description: "Không tìm thấy item trong giỏ (404041)"
            )
        ]
    )]
    public function update(UpdateCartItemRequest $request, string $itemUuid): JsonResponse
    {
        $data = $this->cartService->updateItem($itemUuid, (int)$request->input('quantity'), $request->user()->id);
        return $this->successResponse($data, 'Cập nhật giỏ hàng thành công');
    }

    #[OA\Delete(
        path: "/carts/{itemUuid}",
        summary: "Xóa 1 sản phẩm khỏi giỏ",
        security: [['bearerAuth' => []]],
        tags: ["Cart"],
        parameters: [
            new OA\Parameter(name: "itemUuid", in: "path", required: true, schema: new OA\Schema(type: "string", format: "uuid"))
        ],
        responses: [
            new OA\Response(
                response: 200, 
                description: "Xóa thành công",
                content: new OA\JsonContent(properties: [
                    new OA\Property(property: "success", type: "boolean", example: true),
                    new OA\Property(property: "data", type: "object", description: "Cart Object updated")
                ])
            ),
            new OA\Response(response: 404, description: "Item not found (404041)")
        ]
    )]
    public function destroy(string $itemUuid): JsonResponse
    {
        $data = $this->cartService->removeItem($itemUuid, request()->user()->id);
        return $this->successResponse($data, 'Đã xóa sản phẩm khỏi giỏ');
    }

    #[OA\Post(
        path: "/carts/bulk-delete",
        summary: "Xóa nhiều sản phẩm (Checkbox)",
        description: "Dùng khi user tích chọn nhiều sản phẩm rồi nhấn xóa.",
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
                        example: ["uuid-1", "uuid-2"]
                    )
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: "Xóa thành công")
        ]
    )]
    public function bulkDestroy(Request $request): JsonResponse
    {
        $request->validate(['uuids' => 'required|array', 'uuids.*' => 'required|string']);
        
        $this->cartService->removeItemsList($request->user()->id, $request->input('uuids'));
        $data = $this->cartService->getMyCart($request->user()->id);
        
        return $this->successResponse($data, 'Đã xóa các sản phẩm đã chọn');
    }

    #[OA\Delete(
        path: "/carts",
        summary: "Làm trống giỏ hàng (Clear All)",
        security: [['bearerAuth' => []]],
        tags: ["Cart"],
        responses: [
            new OA\Response(
                response: 200, 
                description: "Đã làm trống",
                content: new OA\JsonContent(properties: [
                    new OA\Property(property: "success", type: "boolean", example: true),
                    new OA\Property(property: "data", type: "null")
                ])
            )
        ]
    )]
    public function clear(Request $request): JsonResponse
    {
        $this->cartService->clearCart($request->user()->id);
        return $this->successResponse(null, 'Giỏ hàng đã được làm trống');
    }
}