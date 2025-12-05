<?php

declare(strict_types=1);

namespace Modules\Shared\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Modules\Shared\Services\BaseService;
use Modules\Shared\Http\Resources\ApiResponse;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use OpenApi\Attributes as OA;

#[OA\Info(
    version: "1.0.0",
    title: "E-commerce Modular API",
    description: "Tài liệu API hệ thống E-commerce",
    contact: new OA\Contact(email: "admin@system.com")
)]
#[OA\Server(url: "/api", description: "API Server")]
#[OA\SecurityScheme(
    securityScheme: "bearerAuth",
    type: "http",
    scheme: "bearer",
    bearerFormat: "JWT"
)]
#[OA\Schema(
    schema: "ApiResponse",
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
        
        $perPage = ($perPage > 100 || $perPage < 1) ? 15 : $perPage;

        $data = $this->service->paginate($perPage);

        return response()->json(ApiResponse::paginated($data));
    }

    public function show(string $uuid): JsonResponse
    {
        $data = $this->service->findByUuidOrFail($uuid);
        return response()->json(ApiResponse::success($data));
    }

    public function destroy(string $uuid): JsonResponse
    {
        $this->service->delete($uuid);
        return response()->json(ApiResponse::success(null, 'Deleted successfully'));
    }

    protected function validateRequest(Request $request): array
    {
        if (!$request instanceof FormRequest) {
            return $request->validate($this->getValidationRules());
        }

        return $request->validated();
    }

    protected function getValidationRules(): array
    {
        return [];
    }
}