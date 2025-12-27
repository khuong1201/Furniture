<?php

declare(strict_types=1);

namespace Modules\Cart\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Modules\Shared\Http\Controllers\BaseController;
use Modules\Cart\Services\CartService;
use Modules\Shared\Http\Traits\ApiResponseTrait;
use OpenApi\Attributes as OA;

#[OA\Tag(name: "Cart Voucher", description: "Quản lý mã giảm giá trong giỏ hàng")]
class CartVoucherController extends BaseController
{
    public function __construct(protected CartService $cartService)
    {
        parent::__construct($cartService);
    }

    #[OA\Post(
        path: "/api/carts/apply-coupon",
        summary: "Áp dụng mã giảm giá",
        description: "Kiểm tra mã voucher và áp dụng vào giỏ hàng hiện tại.",
        security: [['bearerAuth' => []]],
        tags: ["Cart Voucher"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["code"],
                properties: [
                    new OA\Property(property: "code", type: "string", example: "SUMMER2025", description: "Mã voucher")
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200, 
                description: "Áp dụng thành công",
                content: new OA\JsonContent(properties: [
                    new OA\Property(property: "success", type: "boolean", example: true),
                    new OA\Property(property: "data", type: "object", description: "Object Giỏ hàng mới nhất sau khi giảm giá")
                ])
            ),
            new OA\Response(
                response: 404, 
                description: "Giỏ hàng trống hoặc Không tìm thấy Voucher (404210)",
                content: new OA\JsonContent(properties: [
                    new OA\Property(property: "success", type: "boolean", example: false),
                    new OA\Property(property: "error_code", type: "integer", example: 404210),
                    new OA\Property(property: "message", type: "string", example: "Voucher not found")
                ])
            ),
            new OA\Response(
                response: 400, 
                description: "Voucher hết hạn hoặc không đủ điều kiện (400211)",
                content: new OA\JsonContent(properties: [
                    new OA\Property(property: "success", type: "boolean", example: false),
                    new OA\Property(property: "error_code", type: "integer", example: 400211),
                    new OA\Property(property: "message", type: "string", example: "Voucher expired")
                ])
            )
        ]
    )]
    public function apply(Request $request): JsonResponse
    {
        $request->validate(['code' => 'required|string|max:50']);
        
        // Gọi Service xử lý logic check voucher
        $cart = $this->cartService->applyVoucher($request->user()->id, $request->input('code'));
        
        return $this->successResponse($cart, 'Áp dụng mã giảm giá thành công');
    }

    #[OA\Delete(
        path: "/api/carts/remove-coupon",
        summary: "Gỡ mã giảm giá",
        security: [['bearerAuth' => []]],
        tags: ["Cart Voucher"],
        responses: [
            new OA\Response(
                response: 200, 
                description: "Gỡ thành công",
                content: new OA\JsonContent(properties: [
                    new OA\Property(property: "success", type: "boolean", example: true),
                    new OA\Property(property: "data", type: "object", description: "Giỏ hàng sau khi gỡ voucher")
                ])
            )
        ]
    )]
    public function remove(Request $request): JsonResponse
    {
        $cart = $this->cartService->removeVoucher($request->user()->id);
        return $this->successResponse($cart, 'Đã gỡ mã giảm giá');
    }
}