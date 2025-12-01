<?php

namespace Modules\Address\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Shared\Http\Controllers\BaseController;
use Modules\Shared\Http\Resources\ApiResponse;
use Modules\Address\Services\AddressService;
use Modules\Address\Http\Requests\StoreAddressRequest;
use Modules\Address\Http\Requests\UpdateAddressRequest;

class AddressController extends BaseController
{
    public function __construct(AddressService $service) 
    {
        parent::__construct($service);
    }

    public function index(Request $request): JsonResponse
    {
        $userId = $request->user()->id;
        $data = $this->service->listForUser($userId);
        
        return response()->json(ApiResponse::success($data));
    }

    public function store(Request $request): JsonResponse
    {
        $validatedRequest = app(StoreAddressRequest::class);
        
        $data = $validatedRequest->validated();
        $data['user_id'] = $request->user()->id; 

        $address = $this->service->create($data);
        
        return response()->json(ApiResponse::success($address, 'Address created successfully', 201), 201);
    }

    public function update(Request $request, string $uuid): JsonResponse
    {
        $validatedRequest = app(UpdateAddressRequest::class);

        $userId = $request->user()->id;
        $address = $this->service->findByUuidOrFail($uuid);
        if ($address->user_id !== $userId) {
            return response()->json(ApiResponse::error('Unauthorized access to this address', 403), 403);
        }

        $updated = $this->service->update($uuid, $validatedRequest->validated());
        
        return response()->json(ApiResponse::success($updated, 'Address updated successfully'));
    }

    public function destroy(string $uuid): JsonResponse 
    {
        $userId = request()->user()->id;
        
        $this->service->deleteForUser($uuid, $userId);
        
        return response()->json(ApiResponse::success(null, 'Address deleted successfully'));
    }
}