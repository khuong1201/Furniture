<?php

declare(strict_types=1);

namespace Modules\Promotion\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Modules\Shared\Http\Controllers\BaseController;
use Modules\Shared\Http\Resources\ApiResponse;
use Modules\Promotion\Services\PromotionService;
use Modules\Promotion\Http\Requests\StorePromotionRequest;
use Modules\Promotion\Http\Requests\UpdatePromotionRequest;
use Modules\Promotion\Domain\Models\Promotion;
use OpenApi\Attributes as OA;

#[OA\Tag(
    name: "Promotions",
    description: "Quản lý chương trình khuyến mãi, giảm giá"
)]
class PromotionController extends BaseController
{
    public function __construct(PromotionService $service)
    {
        parent::__construct($service);
    }

    #[OA\Get(
        path: "/admin/promotions",
        summary: "Lấy danh sách khuyến mãi",
        security: [["bearerAuth" => []]],
        tags: ["Promotions"],
        parameters: [
            new OA\Parameter(name: "page", in: "query", schema: new OA\Schema(type: "integer")),
            new OA\Parameter(name: "per_page", in: "query", schema: new OA\Schema(type: "integer")),
            new OA\Parameter(name: "search", in: "query", schema: new OA\Schema(type: "string")),
            new OA\Parameter(name: "is_active", in: "query", schema: new OA\Schema(type: "boolean")),
        ],
        responses: [ new OA\Response(response: 200, description: "Success") ]
    )]
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Promotion::class);

        $data = $this->service->paginate($request->integer('per_page', 15), $request->all());
        return response()->json(ApiResponse::paginated($data));
    }

    #[OA\Post(
        path: "/admin/promotions",
        summary: "Tạo mới khuyến mãi",
        security: [["bearerAuth" => []]],
        tags: ["Promotions"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["name", "type", "value", "start_date", "end_date"],
                properties: [
                    new OA\Property(property: "name", type: "string", example: "Summer Sale"),
                    new OA\Property(property: "description", type: "string"),
                    new OA\Property(property: "type", type: "string", enum: ["percentage", "fixed"]),
                    new OA\Property(property: "value", type: "number", example: 10),
                    new OA\Property(property: "start_date", type: "string", format: "date-time"),
                    new OA\Property(property: "end_date", type: "string", format: "date-time"),
                    new OA\Property(property: "product_ids", type: "array", items: new OA\Items(type: "integer")),
                    new OA\Property(property: "is_active", type: "boolean", default: true),
                    new OA\Property(property: "min_order_value", type: "number"),
                    new OA\Property(property: "max_discount_amount", type: "number"),
                ]
            )
        ),
        responses: [ new OA\Response(response: 201, description: "Created") ]
    )]
    public function store(StorePromotionRequest $request): JsonResponse
    {
        $this->authorize('create', Promotion::class);

        $promotion = $this->service->create($request->validated());
        return response()->json(ApiResponse::success($promotion, 'Promotion created', 201), 201);
    }

    #[OA\Get(
        path: "/admin/promotions/{uuid}",
        summary: "Xem chi tiết khuyến mãi",
        security: [["bearerAuth" => []]],
        tags: ["Promotions"],
        parameters: [
            new OA\Parameter(name: "uuid", in: "path", required: true, schema: new OA\Schema(type: "string", format: "uuid"))
        ],
        responses: [ new OA\Response(response: 200, description: "Success") ]
    )]
    public function show(string $uuid): JsonResponse
    {
        $promotion = $this->service->findByUuidOrFail($uuid);
        $this->authorize('view', $promotion);
        
        $promotion->load('products'); 
        return response()->json(ApiResponse::success($promotion));
    }

    #[OA\Put(
        path: "/admin/promotions/{uuid}",
        summary: "Cập nhật khuyến mãi",
        security: [["bearerAuth" => []]],
        tags: ["Promotions"],
        parameters: [
            new OA\Parameter(name: "uuid", in: "path", required: true, schema: new OA\Schema(type: "string", format: "uuid"))
        ],
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "name", type: "string"),
                    new OA\Property(property: "is_active", type: "boolean"),
                    new OA\Property(property: "product_ids", type: "array", items: new OA\Items(type: "integer"))
                ]
            )
        ),
        responses: [ new OA\Response(response: 200, description: "Updated") ]
    )]
    public function update(UpdatePromotionRequest $request, string $uuid): JsonResponse
    {
        $promotionModel = $this->service->findByUuidOrFail($uuid);
        $this->authorize('update', $promotionModel);

        $promotion = $this->service->update($uuid, $request->validated());
        return response()->json(ApiResponse::success($promotion, 'Promotion updated'));
    }

    #[OA\Delete(
        path: "/admin/promotions/{uuid}",
        summary: "Xóa khuyến mãi",
        security: [["bearerAuth" => []]],
        tags: ["Promotions"],
        parameters: [
            new OA\Parameter(name: "uuid", in: "path", required: true, schema: new OA\Schema(type: "string", format: "uuid"))
        ],
        responses: [ new OA\Response(response: 200, description: "Deleted") ]
    )]
    public function destroy(string $uuid): JsonResponse
    {
        $promotionModel = $this->service->findByUuidOrFail($uuid);
        $this->authorize('delete', $promotionModel);

        $this->service->delete($uuid);
        return response()->json(ApiResponse::success(null, 'Promotion deleted'));
    }
}