<?php

namespace Modules\Warehouse\Http\Controllers;

use Modules\Shared\Http\Controllers\BaseController;
use Modules\Shared\Http\Resources\ApiResponse;
use Modules\Warehouse\Services\WarehouseService;
use Modules\Warehouse\Http\Requests\StoreWarehouseRequest;
use Modules\Warehouse\Http\Requests\UpdateWarehouseRequest;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
class WarehouseController extends BaseController
{
    public function __construct(WarehouseService $service)
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
        $request = app(StoreWarehouseRequest::class);
        $data = $this->service->create($request->validated());
        
        return response()->json(ApiResponse::success($data, 'Warehouse created successfully', 201), 201);
    }

    public function update(Request $request, string $uuid): JsonResponse
    {
        $request = app(UpdateWarehouseRequest::class);
        $data = $this->service->update($uuid, $request->validated());

        return response()->json(ApiResponse::success($data, 'Warehouse updated successfully'));
    }
}