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
use OpenApi\Attributes as OA;

#[OA\Tag(name: "Reviews", description: "API quản lý Đánh giá sản phẩm")]
class ReviewController extends BaseController
{
    public function __construct(ReviewService $service)
    {
        parent::__construct($service);
    }

    #[OA\Get(
        path: "/public/reviews",
        summary: "Xem danh sách đánh giá (Khách xem - Bắt buộc có product_uuid)",
        description: "Chỉ trả về các review đã được duyệt (is_approved=true).",
        tags: ["Reviews"],
        parameters: [
            new OA\Parameter(name: "product_uuid", in: "query", required: true, schema: new OA\Schema(type: "string", format: "uuid")),
            new OA\Parameter(name: "page", in: "query", schema: new OA\Schema(type: "integer")),
            new OA\Parameter(name: "per_page", in: "query", schema: new OA\Schema(type: "integer", default: 10)),
            new OA\Parameter(name: "rating", in: "query", schema: new OA\Schema(type: "integer", enum: [1, 2, 3, 4, 5])),
            new OA\Parameter(name: "has_image", in: "query", schema: new OA\Schema(type: "boolean")),
        ],
        responses: [new OA\Response(response: 200, description: "Success")]
    )]
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'product_uuid' => 'required|uuid',
        ]);

        $filters = $request->all();
        $productUuid = $request->query('product_uuid');

        // Logic: Khách chỉ xem được review đã duyệt
        $filters['is_approved'] = true;

        $data = $this->service->listReviewsForProduct($productUuid, $filters);
        
        return response()->json(ApiResponse::paginated($data));
    }

    #[OA\Get(
        path: "/admin/reviews",
        summary: "Quản lý tất cả đánh giá (Admin)",
        description: "Admin xem toàn bộ review, lọc theo trạng thái duyệt, sản phẩm, rating...",
        security: [['bearerAuth' => []]],
        tags: ["Reviews"],
        parameters: [
            new OA\Parameter(name: "page", in: "query", schema: new OA\Schema(type: "integer")),
            new OA\Parameter(name: "per_page", in: "query", schema: new OA\Schema(type: "integer", default: 20)),
            new OA\Parameter(name: "product_uuid", in: "query", schema: new OA\Schema(type: "string", format: "uuid")),
            new OA\Parameter(name: "is_approved", in: "query", schema: new OA\Schema(type: "boolean")),
            new OA\Parameter(name: "rating", in: "query", schema: new OA\Schema(type: "integer")),
        ],
        responses: [new OA\Response(response: 200, description: "Success")]
    )]
    public function adminIndex(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Review::class);

        $filters = $request->all();
        $perPage = $request->integer('per_page', 20);

        $data = $this->service->paginate($perPage, $filters);
        
        return response()->json(ApiResponse::paginated($data));
    }

    #[OA\Get(
        path: "/public/reviews/stats",
        summary: "Lấy thống kê đánh giá (5 sao, 4 sao...)",
        tags: ["Reviews"],
        parameters: [
            new OA\Parameter(name: "product_uuid", in: "query", required: true, schema: new OA\Schema(type: "string", format: "uuid"))
        ],
        responses: [new OA\Response(response: 200, description: "Success")]
    )]
    public function stats(Request $request): JsonResponse
    {
        $request->validate(['product_uuid' => 'required|uuid']);
        
        $stats = $this->service->getReviewStats($request->query('product_uuid'));
        
        return response()->json(ApiResponse::success($stats));
    }

    #[OA\Post(
        path: "/reviews",
        summary: "Tạo đánh giá mới",
        security: [['bearerAuth' => []]],
        tags: ["Reviews"],
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(
                required: ["product_uuid", "rating"],
                properties: [
                    new OA\Property(property: "product_uuid", type: "string", format: "uuid"),
                    new OA\Property(property: "rating", type: "integer", minimum: 1, maximum: 5),
                    new OA\Property(property: "comment", type: "string"),
                    new OA\Property(property: "images", type: "array", items: new OA\Items(type: "string")),
                ]
            )
        ),
        responses: [new OA\Response(response: 201, description: "Created")]
    )]
    public function store(StoreReviewRequest $request): JsonResponse
    {
        $review = $this->service->create($request->validated());
        return response()->json(ApiResponse::success($review, 'Review submitted', 201), 201);
    }

    #[OA\Put(
        path: "/reviews/{uuid}",
        summary: "Cập nhật đánh giá",
        security: [['bearerAuth' => []]],
        tags: ["Reviews"],
        parameters: [new OA\Parameter(name: "uuid", in: "path", required: true, schema: new OA\Schema(type: "string"))],
        requestBody: new OA\RequestBody(content: new OA\JsonContent(properties: [
            new OA\Property(property: "rating", type: "integer"),
            new OA\Property(property: "comment", type: "string"),
            new OA\Property(property: "is_approved", type: "boolean")
        ])),
        responses: [new OA\Response(response: 200, description: "Updated")]
    )]
    public function update(UpdateReviewRequest $request, string $uuid): JsonResponse
    {
        $review = $this->service->findByUuidOrFail($uuid);
        $this->authorize('update', $review);
        
        $updated = $this->service->update($uuid, $request->validated());
        
        return response()->json(ApiResponse::success($updated, 'Updated'));
    }

    #[OA\Delete(
        path: "/reviews/{uuid}",
        summary: "Xóa đánh giá",
        security: [['bearerAuth' => []]],
        tags: ["Reviews"],
        parameters: [new OA\Parameter(name: "uuid", in: "path", required: true, schema: new OA\Schema(type: "string"))],
        responses: [new OA\Response(response: 200, description: "Deleted")]
    )]
    public function destroy(string $uuid): JsonResponse
    {
        $review = $this->service->findByUuidOrFail($uuid);
        $this->authorize('delete', $review);

        $this->service->delete($uuid); 

        return response()->json(ApiResponse::success(null, 'Deleted'));
    }
    
    #[OA\Get(
        path: "/public/reviews/{uuid}", 
        summary: "Xem chi tiết một review", 
        tags: ["Reviews"], 
        parameters: [new OA\Parameter(name: "uuid", in: "path", required: true, schema: new OA\Schema(type: "string"))], 
        responses: [new OA\Response(response: 200, description: "Success")]
    )]
    public function show(string $uuid): JsonResponse
    {
        // [FIX LỖI FATAL ERROR]: Đã bỏ tham số "Request $request" để khớp với BaseController
        $review = $this->service->findByUuidOrFail($uuid);

        // Sử dụng helper request() để lấy thông tin user
        $request = request();

        if (!$review->is_approved) {
            // Khách -> 404
            if (!$request->user()) {
                abort(404);
            }
            // User -> Check quyền
            $this->authorize('view', $review);
        }

        return response()->json(ApiResponse::success($review));
    }
}