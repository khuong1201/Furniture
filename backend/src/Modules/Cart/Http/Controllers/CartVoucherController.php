<?php

declare(strict_types=1);

namespace Modules\Cart\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Modules\Shared\Http\Resources\ApiResponse;
use Modules\Cart\Services\CartService;
use OpenApi\Attributes as OA;

#[OA\Tag(name: "Cart", description: "API Giỏ hàng")]
class CartVoucherController extends Controller
{
    public function __construct(protected CartService $cartService) {}

    #[OA\Post(
        path: "/carts/apply-coupon",
        summary: "Áp dụng mã giảm giá",
        security: [['bearerAuth' => []]],
        tags: ["Cart"],
        requestBody: new OA\RequestBody(content: new OA\JsonContent(
            required: ["code"],
            properties: [new OA\Property(property: "code", type: "string", example: "SALE50")]
        )),
        responses: [new OA\Response(response: 200, description: "Applied")]
    )]
    public function apply(Request $request): JsonResponse
    {
        $request->validate(['code' => 'required|string']);
        
        // Logic này cần tích hợp Module Voucher. Tạm thời giả lập để code chạy.
        // Nếu đã có Module Voucher, uncomment dòng dưới:
        // $result = app(\Modules\Voucher\Services\VoucherService::class)->applyVoucher(...);
        
        return response()->json(ApiResponse::error('Voucher System Integration Pending', 501), 501);
    }

    #[OA\Delete(path: "/carts/remove-coupon", summary: "Gỡ mã giảm giá", security: [['bearerAuth' => []]], tags: ["Cart"], responses: [new OA\Response(response: 200, description: "Removed")])]
    public function remove(Request $request): JsonResponse
    {
        $cart = $this->cartService->getRepository()->findByUser($request->user()->id);
        if ($cart) {
            $cart->update([
                'voucher_code' => null,
                'voucher_discount' => 0
            ]);
        }
        return response()->json(ApiResponse::success(null, 'Voucher removed'));
    }
}