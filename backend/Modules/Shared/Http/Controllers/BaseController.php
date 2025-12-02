<?php

namespace Modules\Shared\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Modules\Shared\Services\BaseService;
use Illuminate\Foundation\Http\FormRequest;
use OpenApi\Attributes as OA;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

#[OA\Info(
    version: "1.0.0",
    title: "E-commerce Modular API",
    description: "Tài liệu API hệ thống E-commerce (Sử dụng PHP 8 Attributes)",
    contact: new OA\Contact(email: "admin@system.com")
)]
#[OA\Server(
    url: "/api",
    description: "API Server"
)]
#[OA\SecurityScheme(
    securityScheme: "bearerAuth",
    type: "http",
    scheme: "bearer",
    bearerFormat: "JWT",
    description: "Nhập Access Token vào đây"
)]

#[OA\Schema(
    schema: "ApiResponse",
    title: "Standard Response",
    properties: [
        new OA\Property(property: "success", type: "boolean", example: true),
        new OA\Property(property: "message", type: "string", example: "Success"),
        new OA\Property(property: "data", type: "object", nullable: true)
    ]
)]

abstract class BaseController extends Controller
{
    use AuthorizesRequests;
    protected BaseService $service;

    public function __construct(BaseService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request): JsonResponse
    {
        $perPage = $request->integer('per_page', 15);
        
        // Giới hạn per_page để tránh overload
        $perPage = min($perPage, 100);
        
        $data = $this->service->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

    public function show(string $uuid): JsonResponse
    {
        $data = $this->service->findByUuidOrFail($uuid);
        
        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }
    
    // public function store(Request $request): JsonResponse
    // {
    //     $validated = $this->validateRequest($request);
    //     $data = $this->service->create($validated);

    //     return response()->json([
    //         'success' => true,
    //         'message' => 'Created successfully',
    //         'data' => $data
    //     ], 201);
    // }

    // public function update(Request $request, string $uuid): JsonResponse
    // {
    //     $validated = $this->validateRequest($request);
    //     $data = $this->service->update($uuid, $validated);

    //     return response()->json([
    //         'success' => true,
    //         'message' => 'Updated successfully',
    //         'data' => $data
    //     ]);
    // }

    public function destroy(string $uuid): JsonResponse
    {
        $this->service->delete($uuid);
        
        return response()->json([
            'success' => true,
            'message' => 'Deleted successfully'
        ]);
    }

    protected function validateRequest(Request $request): array
    {
        if (!$request instanceof FormRequest) {
            throw new \LogicException(
                'Request must be a FormRequest instance. Create a FormRequest class for validation.'
            );
        }

        return $request->validated();
    }
    
    protected function getValidationRules(): array
    {
        return [];
    }
}