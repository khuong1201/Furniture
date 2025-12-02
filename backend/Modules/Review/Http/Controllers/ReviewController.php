<?php

namespace Modules\Review\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Modules\Shared\Http\Controllers\BaseController;
use Modules\Shared\Http\Resources\ApiResponse;
use Modules\Review\Services\ReviewService;
use Modules\Review\Http\Requests\StoreReviewRequest;
use Modules\Review\Http\Requests\UpdateReviewRequest;
use OpenApi\Attributes as OA;

#[OA\Tag(
    name: "Reviews",
    description: "API quản lý Đánh giá sản phẩm (User tạo/sửa/xóa của chính mình)"
)]

class ReviewController extends BaseController
{
    public function __construct(ReviewService $service)
    {
        parent::__construct($service);
    }

    #[OA\Get(
        path: "/api/reviews",
        summary: "Xem danh sách đánh giá",
        tags: ["Reviews"],
        parameters: [
            new OA\Parameter(name: "product_uuid", in: "query", description: "Filter theo UUID sản phẩm", required: false, schema: new OA\Schema(type: "string", format: "uuid")),
            new OA\Parameter(name: "page", in: "query", schema: new OA\Schema(type: "integer")),
            new OA\Parameter(name: "per_page", in: "query", schema: new OA\Schema(type: "integer")),
        ],
        responses: [
            new OA\Response(response: 200, description: "Successful operation")
        ]
    )]

    public function index(Request $request): JsonResponse
    {
        $filters = $request->all();
        $data = $this->service->getRepository()->with(['user'])->paginate($request->get('per_page', 10), $filters);
        return response()->json(ApiResponse::paginated($data));
    }

    #[OA\Post(
        path: "/api/reviews",
        summary: "Tạo đánh giá mới",
        security: [['bearerAuth' => []]],
        tags: ["Reviews"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["product_uuid", "rating", "content"],
                properties: [
                    new OA\Property(property: "product_uuid", type: "string", format: "uuid"),
                    new OA\Property(property: "rating", type: "integer", minimum: 1, maximum: 5),
                    new OA\Property(property: "content", type: "string", nullable: true),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: "Review created successfully"),
            new OA\Response(response: 422, description: "Validation error (e.g., already reviewed this product)"),
        ]
    )]

    public function store(StoreReviewRequest $request): JsonResponse
    {
        $review = $this->service->create($request->validated());
        
        return response()->json(ApiResponse::success($review, 'Review created successfully', 201), 201);
    }

    #[OA\Put(
        path: "/api/reviews/{uuid}",
        summary: "Cập nhật đánh giá của mình",
        security: [['bearerAuth' => []]],
        tags: ["Reviews"],
        parameters: [
            new OA\Parameter(name: "uuid", in: "path", required: true, schema: new OA\Schema(type: "string", format: "uuid"))
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "rating", type: "integer", minimum: 1, maximum: 5),
                    new OA\Property(property: "content", type: "string", nullable: true),
                    // Admin có thể gửi 'is_approved' nhưng user thường bị bỏ qua
                    new OA\Property(property: "is_approved", type: "boolean", nullable: true), 
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: "Review updated successfully"),
            new OA\Response(response: 403, description: "Forbidden (Not owner or Admin)"),
            new OA\Response(response: 404, description: "Review not found")
        ]
    )]

    public function update(UpdateReviewRequest $request, string $uuid): JsonResponse
    {
        $reviewModel = $this->service->getRepository()->findByUuid($uuid);

        if (!$reviewModel) {
            return response()->json(ApiResponse::error('Review not found', 404), 404);
        }

        $this->authorize('update', $reviewModel);
        
        $review = $this->service->update($uuid, $request->validated());
        
        return response()->json(ApiResponse::success($review, 'Review updated successfully'));
    }

    #[OA\Delete(
        path: "/api/reviews/{uuid}",
        summary: "Xóa đánh giá của mình",
        security: [['bearerAuth' => []]],
        tags: ["Reviews"],
        parameters: [
            new OA\Parameter(name: "uuid", in: "path", required: true, schema: new OA\Schema(type: "string", format: "uuid"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Review deleted successfully"),
            new OA\Response(response: 403, description: "Forbidden (Not owner or Admin)"),
            new OA\Response(response: 404, description: "Review not found")
        ]
    )]
    
    public function destroy(string $uuid): JsonResponse
    {
        $reviewModel = $this->service->getRepository()->findByUuid($uuid);

        if (!$reviewModel) {
            return response()->json(ApiResponse::error('Review not found', 404), 404);
        }

        $this->authorize('delete', $reviewModel);

        $this->service->delete($uuid); 

        return response()->json(ApiResponse::success(null, 'Review deleted successfully'));
    }
}