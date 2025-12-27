<?php

declare(strict_types=1);

namespace Modules\Promotion\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Modules\Shared\Http\Controllers\BaseController;
use Modules\Shared\Http\Traits\ApiResponseTrait;
use Modules\Promotion\Services\PromotionService;
use Modules\Promotion\Http\Requests\StorePromotionRequest;
use Modules\Promotion\Http\Requests\UpdatePromotionRequest;
use Modules\Promotion\Domain\Models\Promotion;
use OpenApi\Attributes as OA;

#[OA\Tag(
    name: "Promotions",
    description: "Quản lý chương trình khuyến mãi, giảm giá (BigInteger Support)"
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
            new OA\Parameter(name: "valid_now", in: "query", schema: new OA\Schema(type: "boolean", description: "Chỉ lấy khuyến mãi đang chạy")),
        ],
        responses: [ new OA\Response(response: 200, description: "Success") ]
    )]
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Promotion::class);

        // Cast per_page đảm bảo int
        $data = $this->service->paginate($request->integer('per_page', 15), $request->all());
        return $this->successResponse($data);
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
                    new OA\Property(property: "name", type: "string", example: "Flash Sale 11/11"),
                    new OA\Property(property: "type", type: "string", enum: ["percentage", "fixed"]),
                    new OA\Property(property: "value", type: "integer", example: 10, description: "10% hoặc 50000 VND"),
                    new OA\Property(property: "start_date", type: "string", format: "date-time"),
                    new OA\Property(property: "end_date", type: "string", format: "date-time"),
                    new OA\Property(property: "product_ids", type: "array", items: new OA\Items(type: "integer")),
                    new OA\Property(property: "is_active", type: "boolean", default: true),
                ]
            )
        ),
        responses: [ new OA\Response(response: 201, description: "Created") ]
    )]
    public function store(StorePromotionRequest $request): JsonResponse
    {
        $this->authorize('create', Promotion::class);

        $promotion = $this->service->create($request->validated());
        return $this->successResponse($promotion, 'Promotion created', 201);
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
        return $this->successResponse($promotion);
    }

    #[OA\Put(
        path: "/admin/promotions/{uuid}",
        summary: "Cập nhật khuyến mãi",
        security: [["bearerAuth" => []]],
        tags: ["Promotions"],
        requestBody: new OA\RequestBody(content: new OA\JsonContent(properties: [])), 
        responses: [ new OA\Response(response: 200, description: "Updated") ]
    )]
    public function update(UpdatePromotionRequest $request, string $uuid): JsonResponse
    {
        $promotionModel = $this->service->findByUuidOrFail($uuid);
        $this->authorize('update', $promotionModel);

        $promotion = $this->service->update($uuid, $request->validated());
        return $this->successResponse($promotion, 'Promotion updated');
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
        return $this->successResponse(null, 'Promotion deleted');
    }
}