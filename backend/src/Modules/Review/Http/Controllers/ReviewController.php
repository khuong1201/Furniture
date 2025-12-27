<?php

declare(strict_types=1);

namespace Modules\Review\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Modules\Shared\Http\Controllers\BaseController;
use Modules\Shared\Http\Resources\ApiResponse;
use Modules\Review\Services\ReviewService;
use Modules\Review\Http\Requests\StoreReviewRequest;
use Modules\Review\Http\Requests\UpdateReviewRequest;
use Modules\Review\Domain\Models\Review;
use Modules\Review\Http\Resources\ReviewResource; // [NEW]
use OpenApi\Attributes as OA;

#[OA\Tag(name: "Reviews", description: "API Đánh giá & Nhận xét sản phẩm")]
class ReviewController extends BaseController
{
    public function __construct(protected ReviewService $reviewService)
    {
        parent::__construct($reviewService);
    }

    #[OA\Get(
        path: "/api/public/reviews",
        summary: "Xem đánh giá (Public)",
        description: "Lấy danh sách đánh giá của một sản phẩm. Chỉ trả về đánh giá đã được duyệt.",
        tags: ["Reviews"],
        parameters: [
            new OA\Parameter(name: "product_uuid", in: "query", required: true, schema: new OA\Schema(type: "string", format: "uuid")),
            new OA\Parameter(name: "rating", in: "query", description: "Lọc theo số sao (1-5)", schema: new OA\Schema(type: "integer", enum: [1,2,3,4,5])),
            new OA\Parameter(name: "has_image", in: "query", description: "Chỉ lấy review có ảnh", schema: new OA\Schema(type: "boolean")),
            new OA\Parameter(name: "page", in: "query", schema: new OA\Schema(type: "integer")),
            new OA\Parameter(name: "per_page", in: "query", schema: new OA\Schema(type: "integer", default: 10)),
        ],
        responses: [
            new OA\Response(
                response: 200, 
                description: "Success",
                content: new OA\JsonContent(properties: [
                    new OA\Property(property: "success", type: "boolean", example: true),
                    new OA\Property(
                        property: "data", 
                        type: "array", 
                        items: new OA\Items(properties: [
                            new OA\Property(property: "uuid", type: "string"),
                            new OA\Property(property: "rating", type: "integer", example: 5),
                            new OA\Property(property: "comment", type: "string"),
                            new OA\Property(property: "images", type: "array", items: new OA\Items(type: "string")),
                            new OA\Property(property: "is_verified_purchase", type: "boolean"),
                            new OA\Property(property: "user", type: "object", properties: [
                                new OA\Property(property: "name", type: "string"),
                                new OA\Property(property: "avatar", type: "string"),
                            ]),
                            new OA\Property(property: "created_at", type: "string")
                        ])
                    ),
                    new OA\Property(property: "meta", type: "object")
                ])
            )
        ]
    )]
    public function index(Request $request): JsonResponse
    {
        $request->validate(['product_uuid' => 'required|uuid']);

        $filters = $request->all();
        $productUuid = $request->query('product_uuid');
        
        // Public API luôn chỉ lấy bài đã duyệt
        $filters['is_approved'] = true; 

        $paginator = $this->reviewService->listReviewsForProduct($productUuid, $filters);
        
        // Sử dụng Resource để format chuẩn
        $paginator->through(fn($review) => new ReviewResource($review));
        
        return $this->successResponse($paginator);
    }

    #[OA\Get(
        path: "/api/public/reviews/stats",
        summary: "Thống kê sao (Stats)",
        description: "Trả về số lượng đánh giá, điểm trung bình và phân bố sao (5 sao bao nhiêu người, 4 sao bao nhiêu...).",
        tags: ["Reviews"],
        parameters: [new OA\Parameter(name: "product_uuid", in: "query", required: true, schema: new OA\Schema(type: "string", format: "uuid"))],
        responses: [
            new OA\Response(
                response: 200, 
                description: "Success",
                content: new OA\JsonContent(properties: [
                    new OA\Property(property: "success", type: "boolean", example: true),
                    new OA\Property(property: "data", type: "object", properties: [
                        new OA\Property(property: "total_reviews", type: "integer", example: 100),
                        new OA\Property(property: "average_rating", type: "number", example: 4.5),
                        new OA\Property(property: "distribution", type: "array", items: new OA\Items(properties: [
                            new OA\Property(property: "star", type: "integer", example: 5),
                            new OA\Property(property: "count", type: "integer", example: 80),
                            new OA\Property(property: "percent", type: "number", example: 80.0)
                        ]))
                    ])
                ])
            )
        ]
    )]
    public function stats(Request $request): JsonResponse
    {
        $request->validate(['product_uuid' => 'required|uuid']);
        $stats = $this->reviewService->getReviewStats($request->query('product_uuid'));
        return $this->successResponse($stats);
    }

    #[OA\Post(
        path: "/api/reviews",
        summary: "Gửi đánh giá mới",
        security: [['bearerAuth' => []]],
        tags: ["Reviews"],
        requestBody: new OA\RequestBody(content: new OA\JsonContent(
            required: ["product_uuid", "rating"],
            properties: [
                new OA\Property(property: "product_uuid", type: "string", format: "uuid"),
                new OA\Property(property: "rating", type: "integer", minimum: 1, maximum: 5),
                new OA\Property(property: "comment", type: "string"),
                new OA\Property(property: "images", type: "array", items: new OA\Items(type: "string", format: "url"))
            ]
        )),
        responses: [
            new OA\Response(response: 201, description: "Created"),
            new OA\Response(response: 409, description: "Already reviewed (409181)"),
            new OA\Response(response: 422, description: "Validation Error")
        ]
    )]
    public function store(StoreReviewRequest $request): JsonResponse
    {
        $review = $this->reviewService->create($request->validated());
        
        return $this->successResponse(new ReviewResource($review), 'Cảm ơn bạn đã đánh giá', 201);
    }

    #[OA\Get(
        path: "/api/admin/reviews",
        summary: "Quản lý đánh giá (Admin)",
        description: "Xem tất cả đánh giá bao gồm cả chưa duyệt.",
        security: [['bearerAuth' => []]],
        tags: ["Reviews"],
        parameters: [
            new OA\Parameter(name: "page", in: "query", schema: new OA\Schema(type: "integer")),
            new OA\Parameter(name: "is_approved", in: "query", schema: new OA\Schema(type: "boolean")),
            new OA\Parameter(name: "rating", in: "query", schema: new OA\Schema(type: "integer")),
        ],
        responses: [new OA\Response(response: 200, description: "Success")]
    )]
    public function adminIndex(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Review::class);
        
        $paginator = $this->reviewService->paginate($request->integer('per_page', 20), $request->all());
        $paginator->through(fn($review) => new ReviewResource($review)); // Dùng chung Resource

        return $this->successResponse($paginator);
    }

    #[OA\Get(
        path: "/api/public/reviews/{uuid}", 
        summary: "Chi tiết đánh giá", 
        tags: ["Reviews"], 
        parameters: [new OA\Parameter(name: "uuid", in: "path", required: true, schema: new OA\Schema(type: "string"))], 
        responses: [new OA\Response(response: 200, description: "Success")]
    )]
    public function show(string $uuid): JsonResponse
    {
        $review = $this->reviewService->findByUuidOrFail($uuid);

        // Logic check quyền xem: Nếu chưa duyệt thì chỉ chủ sở hữu hoặc admin mới thấy
        if (!$review->is_approved) {
            $user = request()->user();
            if (!$user) {
                // Khách xem bài chưa duyệt -> 404
                return $this->errorResponse('Review not found', 404, null, 404);
            }
            $this->authorize('view', $review);
        }

        return $this->successResponse(new ReviewResource($review));
    }

    #[OA\Put(
        path: "/api/admin/reviews/{uuid}",
        summary: "Duyệt/Sửa đánh giá",
        security: [['bearerAuth' => []]],
        tags: ["Reviews"],
        parameters: [new OA\Parameter(name: "uuid", in: "path", required: true)],
        requestBody: new OA\RequestBody(content: new OA\JsonContent(properties: [
            new OA\Property(property: "is_approved", type: "boolean", description: "Duyệt hoặc Ẩn bài"),
            new OA\Property(property: "comment", type: "string", description: "Admin có thể sửa nội dung nếu vi phạm"),
        ])),
        responses: [new OA\Response(response: 200, description: "Updated")]
    )]
    public function update(UpdateReviewRequest $request, string $uuid): JsonResponse
    {
        $review = $this->reviewService->findByUuidOrFail($uuid);
        $this->authorize('update', $review);
        
        $updated = $this->reviewService->update($uuid, $request->validated());
        
        return $this->successResponse(new ReviewResource($updated), 'Cập nhật đánh giá thành công');
    }

    #[OA\Delete(
        path: "/api/admin/reviews/{uuid}",
        summary: "Xóa đánh giá",
        security: [['bearerAuth' => []]],
        tags: ["Reviews"],
        responses: [new OA\Response(response: 200, description: "Deleted")]
    )]
    public function destroy(string $uuid): JsonResponse
    {
        $review = $this->reviewService->findByUuidOrFail($uuid);
        $this->authorize('delete', $review);
        
        $this->reviewService->delete($uuid); 
        return $this->successResponse(null, 'Đã xóa đánh giá');
    }
}