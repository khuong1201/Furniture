<?php

namespace Modules\Promotion\Http\Controllers;

use Illuminate\Http\Request; 
use Illuminate\Http\JsonResponse;
use Modules\Shared\Http\Controllers\BaseController;
use Modules\Shared\Http\Resources\ApiResponse;
use Modules\Promotion\Services\PromotionService;
use Modules\Promotion\Http\Requests\StorePromotionRequest;
use Modules\Promotion\Http\Requests\UpdatePromotionRequest;

class PromotionController extends BaseController
{
    public function __construct(PromotionService $service)
    {
        parent::__construct($service);
    }

    public function index(Request $request): JsonResponse
    {
        $data = $this->service->paginate($request->get('per_page', 15), $request->all());
        return response()->json(ApiResponse::paginated($data));
    }

    public function store(StorePromotionRequest $request): JsonResponse
    {
        $promotion = $this->service->create($request->validated());
        
        return response()->json(ApiResponse::success($promotion, 'Promotion created', 201), 201);
    }

    public function update(UpdatePromotionRequest $request, string $uuid): JsonResponse
    {
        
        $promotion = $this->service->update($uuid, $request->validated());
        
        return response()->json(ApiResponse::success($promotion, 'Promotion updated'));
    }
}