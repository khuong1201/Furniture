<?php

namespace Modules\Review\Http\Controllers;

use Illuminate\Http\Request; 
use Illuminate\Http\JsonResponse;
use Modules\Shared\Http\Controllers\BaseController;
use Modules\Shared\Http\Resources\ApiResponse;
use Modules\Review\Services\ReviewService;
use Modules\Review\Http\Requests\StoreReviewRequest;
use Modules\Review\Http\Requests\UpdateReviewRequest;

class ReviewController extends BaseController
{
    public function __construct(ReviewService $service)
    {
        parent::__construct($service);
    }

    public function index(Request $request): JsonResponse
    {
        $filters = $request->all();

        $data = $this->service->paginate($request->get('per_page', 10), $filters);
        return response()->json(ApiResponse::paginated($data));
    }

    public function store(Request $request): JsonResponse
    {
        $validatedRequest = app(StoreReviewRequest::class);
        
        $review = $this->service->create($validatedRequest->validated());
        
        return response()->json(ApiResponse::success($review, 'Review created successfully', 201), 201);
    }

    public function update(Request $request, string $uuid): JsonResponse
    {
        $validatedRequest = app(UpdateReviewRequest::class);
        
        $review = $this->service->update($uuid, $validatedRequest->validated());
        
        return response()->json(ApiResponse::success($review, 'Review updated successfully'));
    }

    public function destroy(string $uuid): JsonResponse
    {
        $this->service->delete($uuid); 
        return response()->json(ApiResponse::success(null, 'Review deleted successfully'));
    }
}