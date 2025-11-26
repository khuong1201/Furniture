<?php

namespace Modules\Shared\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Modules\Shared\Services\BaseService;

abstract class BaseController extends Controller
{
    protected BaseService $service;

    public function __construct(BaseService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request): JsonResponse
    {
        $perPage = $request->get('per_page', 15);
        $data = $this->service->paginate($perPage);

        return response()->json($data);
    }

    public function show(string $uuid): JsonResponse
    {
        $data = $this->service->findByUuidOrFail($uuid);
        return response()->json($data);
    }
    
    public function store(Request $request): JsonResponse
    {
        $validated = $this->validateData($request);
        $data = $this->service->create($validated);

        return response()->json($data, 201);
    }

    public function update(Request $request, string $uuid): JsonResponse
    {
        $validated = $this->validateData($request);
        $data = $this->service->update($uuid, $validated);

        return response()->json($data);
    }

    public function destroy(string $uuid): JsonResponse
    {
        $this->service->delete($uuid);
        return response()->json(['message' => 'Deleted successfully']);
    }

    /**
     * Override ở module con nếu có rules riêng.
     */
    protected function validateData(Request $request): array
    {
        return $request->all();
    }
}
