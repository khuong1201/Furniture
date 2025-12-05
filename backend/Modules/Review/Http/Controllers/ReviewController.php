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
        path: "/reviews",
        summary: "Xem danh sách đánh giá (Public/Admin)",
        tags: ["Reviews"],
        parameters: [
            new OA\Parameter(name: "product_uuid", in: "query", schema: new OA\Schema(type: "string", format: "uuid")),
            new OA\Parameter(name: "page", in: "query", schema: new OA\Schema(type: "integer")),
            new OA\Parameter(name: "rating", in: "query", schema: new OA\Schema(type: "integer")),
            new OA\Parameter(name: "admin_view", in: "query", schema: new OA\Schema(type: "boolean"), description: "Nếu true (và là admin) sẽ thấy cả review chưa duyệt"),
        ],
        responses: [ new OA\Response(response: 200, description: "Success") ]
    )]
    public function index(Request $request): JsonResponse
    {
        $filters = $request->all();

        // Nếu user thường gọi, force chỉ xem được approved
        if (!$request->user() || !$request->user()->hasPermissionTo('review.view_all')) {
            unset($filters['admin_view']);
            $filters['is_approved'] = true;
        }

        $data = $this->service->paginate($request->integer('per_page', 10), $filters);
        
        return response()->json(ApiResponse::paginated($data));
    }

    #[OA\Post(
        path: "/reviews",
        summary: "Tạo đánh giá mới",
        security: [['bearerAuth' => []]],
        tags: ["Reviews"],
        requestBody: new OA\RequestBody(
            required: true,
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
        responses: [ new OA\Response(response: 201, description: "Created") ]
    )]
    public function store(StoreReviewRequest $request): JsonResponse
    {
        $review = $this->service->create($request->validated());
        return response()->json(ApiResponse::success($review, 'Review submitted for approval', 201), 201);
    }

    #[OA\Put(
        path: "/reviews/{uuid}",
        summary: "Cập nhật đánh giá",
        security: [['bearerAuth' => []]],
        tags: ["Reviews"],
        parameters: [ new OA\Parameter(name: "uuid", in: "path", required: true, schema: new OA\Schema(type: "string")) ],
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "rating", type: "integer"),
                    new OA\Property(property: "comment", type: "string"),
                    new OA\Property(property: "is_approved", type: "boolean", description: "Admin only"),
                ]
            )
        ),
        responses: [ new OA\Response(response: 200, description: "Updated") ]
    )]
    public function update(UpdateReviewRequest $request, string $uuid): JsonResponse
    {
        $review = $this->service->findByUuidOrFail($uuid);

        $this->authorize('update', $review);

        $updatedReview = $this->service->update($uuid, $request->validated());
        
        return response()->json(ApiResponse::success($updatedReview, 'Review updated successfully'));
    }

    #[OA\Delete(
        path: "/reviews/{uuid}",
        summary: "Xóa đánh giá",
        security: [['bearerAuth' => []]],
        tags: ["Reviews"],
        parameters: [ new OA\Parameter(name: "uuid", in: "path", required: true, schema: new OA\Schema(type: "string")) ],
        responses: [ new OA\Response(response: 200, description: "Deleted") ]
    )]
    public function destroy(string $uuid): JsonResponse
    {
        $review = $this->service->findByUuidOrFail($uuid);

        $this->authorize('delete', $review);

        $this->service->delete($uuid); 

        return response()->json(ApiResponse::success(null, 'Review deleted successfully'));
    }
}