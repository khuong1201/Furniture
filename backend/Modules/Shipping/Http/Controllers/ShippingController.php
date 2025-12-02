<?php

namespace Modules\Shipping\Http\Controllers;

use Illuminate\Http\Request; 
use Illuminate\Http\JsonResponse;
use Modules\Shared\Http\Controllers\BaseController;
use Modules\Shared\Http\Resources\ApiResponse;
use Modules\Shipping\Services\ShippingService;
use Modules\Shipping\Domain\Models\Shipping; 
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
        // Check quyền cơ bản
        $this->authorize('viewAny', Shipping::class);

        $filters = $request->all();

        if (!auth()->user()->hasRole('admin')) {
            $filters['user_id'] = auth()->id();
        }

        $data = $this->service->paginate($request->get('per_page', 15), $filters);
        return response()->json(ApiResponse::paginated($data));
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', Shipping::class);

        $validatedRequest = app(StoreShippingRequest::class);
        
        $shipping = $this->service->create($validatedRequest->validated());
        
        return response()->json(ApiResponse::success($shipping, 'Shipping created', 201), 201);
    }

    public function show(string $uuid): JsonResponse
    {
        $shipping = $this->service->getRepository()->findByUuid($uuid);
        
        if (!$shipping) {
            return response()->json(ApiResponse::error('Shipping not found', 404), 404);
        }

        $this->authorize('view', $shipping);

        $shipping->load('order');

        return response()->json(ApiResponse::success($shipping));
    }

    public function update(Request $request, string $uuid): JsonResponse
    {
        $shipping = $this->service->getRepository()->findByUuid($uuid);
        
        if (!$shipping) {
            return response()->json(ApiResponse::error('Shipping not found', 404), 404);
        }

        $this->authorize('update', $shipping);

        $validatedRequest = app(UpdateShippingRequest::class);
        
        $shipping = $this->service->update($uuid, $validatedRequest->validated());
        
        return response()->json(ApiResponse::success($shipping, 'Shipping updated'));
    }

    public function destroy(string $uuid): JsonResponse
    {
        $shipping = $this->service->getRepository()->findByUuid($uuid);
        
        if (!$shipping) {
            return response()->json(ApiResponse::error('Shipping not found', 404), 404);
        }

        $this->authorize('delete', $shipping);

        $this->service->delete($uuid);

        return response()->json(ApiResponse::success(null, 'Shipping deleted'));
    }
}