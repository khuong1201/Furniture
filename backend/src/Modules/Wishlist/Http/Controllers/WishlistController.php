<?php

declare(strict_types=1);

namespace Modules\Wishlist\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Shared\Http\Controllers\BaseController;
use Modules\Shared\Http\Traits\ApiResponseTrait;
use Modules\Wishlist\Http\Requests\ToggleWishlistRequest;
use Modules\Wishlist\Services\WishlistService;
use OpenApi\Attributes as OA;

#[OA\Tag(name: "Wishlist", description: "API quản lý danh sách yêu thích")]
class WishlistController extends BaseController
{
    public function __construct(WishlistService $service)
    {
        parent::__construct($service);
    }

    #[OA\Get(
        path: "/api/wishlist",
        summary: "Xem danh sách yêu thích của tôi",
        security: [['bearerAuth' => []]],
        tags: ["Wishlist"],
        parameters: [
            new OA\Parameter(name: "page", in: "query", schema: new OA\Schema(type: "integer")),
            new OA\Parameter(name: "per_page", in: "query", schema: new OA\Schema(type: "integer")),
        ],
        responses: [
            new OA\Response(
                response: 200, 
                description: "Success",
                content: new OA\JsonContent(properties: [
                    new OA\Property(property: "success", type: "boolean", example: true),
                    new OA\Property(property: "data", type: "array", items: new OA\Items(
                        properties: [
                            new OA\Property(property: "uuid", type: "string"),
                            new OA\Property(property: "product", type: "object", description: "Thông tin sản phẩm rút gọn")
                        ]
                    ))
                ])
            )
        ]
    )]
    public function index(Request $request): JsonResponse
    {
        $userId = $request->user()->id;
        $data = $this->service->getMyWishlist($userId, $request->integer('per_page', 15));
        
        return $this->successResponse($data);
    }

    #[OA\Post(
        path: "/api/wishlist/toggle",
        summary: "Thêm/Xóa sản phẩm khỏi Wishlist (Toggle)",
        description: "Cơ chế Toggle: Nếu sản phẩm chưa có trong wishlist thì thêm vào, nếu có rồi thì xóa đi.",
        security: [['bearerAuth' => []]],
        tags: ["Wishlist"],
        requestBody: new OA\RequestBody(content: new OA\JsonContent(
            required: ["product_uuid"],
            properties: [
                new OA\Property(property: "product_uuid", type: "string", format: "uuid", example: "uuid-product-123")
            ]
        )),
        responses: [
            new OA\Response(
                response: 200, 
                description: "Toggled Successfully",
                content: new OA\JsonContent(properties: [
                    new OA\Property(property: "success", type: "boolean", example: true),
                    new OA\Property(property: "data", type: "object", properties: [
                        new OA\Property(property: "status", type: "string", example: "added"),
                        new OA\Property(property: "message", type: "string")
                    ])
                ])
            ),
            new OA\Response(response: 404, description: "Product Not Found (404160)")
        ]
    )]
    public function toggle(ToggleWishlistRequest $request): JsonResponse
    {
        $result = $this->service->toggle(
            $request->user()->id, 
            $request->validated()['product_uuid']
        );
        
        return $this->successResponse($result, $result['message']);
    }

    #[OA\Delete(
        path: "/api/wishlist/{uuid}",
        summary: "Xóa 1 item khỏi Wishlist (Theo UUID Wishlist)",
        security: [['bearerAuth' => []]],
        tags: ["Wishlist"],
        parameters: [new OA\Parameter(name: "uuid", in: "path", required: true, schema: new OA\Schema(type: "string"))],
        responses: [new OA\Response(response: 200, description: "Deleted")]
    )]
    public function destroy(string $uuid): JsonResponse
    {
        $wishlist = $this->service->findByUuidOrFail($uuid);
        
        $this->authorize('delete', $wishlist);
        
        $this->service->delete($uuid);
        
        return $this->successResponse(null, 'Item removed from wishlist');
    }
}