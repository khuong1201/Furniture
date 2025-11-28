<?php

namespace Modules\Address\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Address\Http\Requests\StoreAddressRequest;
use Modules\Address\Http\Requests\UpdateAddressRequest;
use Modules\Address\Services\AddressService;

class AddressController extends Controller
{
    public function __construct(protected AddressService $service) {}

    public function index(Request $request): JsonResponse
    {
        $userId = $request->user()->id;
        return response()->json($this->service->listForUser($userId));
    }

    public function store(StoreAddressRequest $request): JsonResponse
    {
        $userId = $request->user()->id;
        $address = $this->service->createForUser($userId, $request->validated());
        return response()->json($address, 201);
    }

    public function update(UpdateAddressRequest $request, string $uuid): JsonResponse
    {
        $userId = $request->user()->id;
        $address = $this->service->updateForUser($uuid, $request->validated(), $userId);
        return response()->json($address);
    }

    public function destroy(string $uuid, Request $request): JsonResponse
    {
        $userId = $request->user()->id;
        $this->service->deleteForUser($uuid, $userId);
        return response()->json(['message' => 'Address deleted successfully']);
    }
}