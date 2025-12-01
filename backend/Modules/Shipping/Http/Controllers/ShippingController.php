<?php

namespace Modules\Shipping\Http\Controllers;

use Illuminate\Http\Request; 
use Illuminate\Http\JsonResponse;
use Modules\Shared\Http\Controllers\BaseController;
use Modules\Shared\Http\Resources\ApiResponse;
use Modules\Shipping\Services\ShippingService;
use Modules\Shipping\Http\Requests\StoreShippingRequest;
use Modules\Shipping\Http\Requests\UpdateShippingRequest;

class ShippingController extends BaseController
{
    public function __construct(ShippingService $service)
    {
        parent::__construct($service);
    }

    public function index(Request $request): JsonResponse
    {
        $data = $this->service->paginate($request->get('per_page', 15), $request->all());
        return response()->json(ApiResponse::paginated($data));
    }

    public function store(Request $request): JsonResponse
    {
        $validatedRequest = app(StoreShippingRequest::class);
        
        $shipping = $this->service->create($validatedRequest->validated());
        
        return response()->json(ApiResponse::success($shipping, 'Shipping created', 201), 201);
    }

    public function update(Request $request, string $uuid): JsonResponse
    {
        $validatedRequest = app(UpdateShippingRequest::class);
        
        $shipping = $this->service->update($uuid, $validatedRequest->validated());
        
        return response()->json(ApiResponse::success($shipping, 'Shipping updated'));
    }
}