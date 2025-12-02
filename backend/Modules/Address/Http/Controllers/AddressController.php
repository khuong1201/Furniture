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

    public function store(StoreAddressRequest $request): JsonResponse
    {
        $validatedData = $request->validated();

        $validatedData['user_id'] = $request->user()->id; 

        $address = $this->service->create($validatedData);
        
        return response()->json(ApiResponse::success($address, 'Address created successfully', 201), 201);
    }

    public function update(UpdateAddressRequest $request, string $uuid): JsonResponse
    {
        $address = $this->service->getRepository()->findByUuid($uuid);
        
        if (!$address) {
            return response()->json(ApiResponse::error('Address not found', 404), 404);
        }

        $this->authorize('update', $address);

        $validatedData = $request->validated();

        $updated = $this->service->update($uuid, $validatedData);
        
        return response()->json(ApiResponse::success($updated, 'Address updated successfully'));
    }

    public function destroy(string $uuid): JsonResponse 
    {
        $address = $this->service->getRepository()->findByUuid($uuid);
        
        if (!$address) {
            return response()->json(ApiResponse::error('Address not found', 404), 404);
        }

        $this->authorize('delete', $address);
        
        $this->service->delete($uuid); 
        
        return response()->json(ApiResponse::success(null, 'Address deleted successfully'));
    }
}