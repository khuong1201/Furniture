<?php

namespace Modules\Shipping\Http\Controllers;

use Illuminate\Routing\Controller;
use Modules\Shipping\Services\ShippingService;
use Modules\Shipping\Http\Requests\StoreShippingRequest;
use Modules\Shipping\Http\Requests\UpdateShippingRequest;
use Illuminate\Http\JsonResponse;

class ShippingController extends Controller
{
    protected ShippingService $service;

    public function __construct(ShippingService $service)
    {
        $this->service = $service;
    }

    public function index(): JsonResponse
    {
        return response()->json($this->service->paginate());
    }

    public function store(StoreShippingRequest $request): JsonResponse
    {
        $shipping = $this->service->create($request->validated());
        return response()->json($shipping, 201);
    }

    public function update(UpdateShippingRequest $request, string $uuid): JsonResponse
    {
        $shipping = $this->service->update($uuid, $request->validated());
        return response()->json($shipping);
    }

    public function destroy(string $uuid): JsonResponse
    {
        $this->service->delete($uuid);
        return response()->json(['message' => 'Shipping deleted successfully']);
    }
}
