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
        return response()->json($this->service->listForUser($request->user()->id));
    }

    public function store(StoreAddressRequest $request): JsonResponse
    {
        $address = $this->service->createForUser($request->user()->id, $request->validated());
        return response()->json($address, 201);
    }

    public function update(UpdateAddressRequest $request, string $uuid): JsonResponse
    {
        $address = $this->service->update($uuid, $request->validated());
        return response()->json($address);
    }

    public function destroy(string $uuid): JsonResponse
    {
        $this->service->delete($uuid);
        return response()->json(['message' => 'Address deleted successfully']);
    }
}
