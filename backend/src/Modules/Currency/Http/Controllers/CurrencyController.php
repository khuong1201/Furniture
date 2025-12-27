<?php

declare(strict_types=1);

namespace Modules\Currency\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Currency\Domain\Models\Currency;
use Modules\Currency\Http\Requests\StoreCurrencyRequest;
use Modules\Currency\Http\Requests\UpdateCurrencyRequest;
use Modules\Currency\Services\CurrencyService;
use Modules\Shared\Http\Controllers\BaseController;
use OpenApi\Attributes as OA;

#[OA\Tag(name: "Currencies", description: "API quản lý Tiền tệ")]
class CurrencyController extends BaseController
{
    public function __construct(CurrencyService $service)
    {
        parent::__construct($service);
    }

    #[OA\Get(
        path: "/api/public/currencies",
        summary: "Lấy danh sách tiền tệ hỗ trợ (Public)",
        tags: ["Currencies"],
        responses: [new OA\Response(response: 200, description: "Success")]
    )]
    public function getActive(): JsonResponse
    {
        $currencies = $this->service->getRepository()->getActiveCurrencies();
        return $this->successResponse($currencies);
    }

    #[OA\Get(
        path: "/api/admin/currencies",
        summary: "Quản lý danh sách tiền tệ (Admin)",
        security: [['bearerAuth' => []]],
        tags: ["Currencies"],
        parameters: [
            new OA\Parameter(name: "page", in: "query", schema: new OA\Schema(type: "integer")),
            new OA\Parameter(name: "search", in: "query", schema: new OA\Schema(type: "string"))
        ],
        responses: [new OA\Response(response: 200, description: "Success")]
    )]
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Currency::class); 
        
        $data = $this->service->paginate($request->integer('per_page', 15)); // BaseController đã handle paginate logic
        return $this->successResponse($data);
    }

    #[OA\Post(
        path: "/api/admin/currencies",
        summary: "Thêm tiền tệ mới",
        security: [['bearerAuth' => []]],
        tags: ["Currencies"],
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(
                required: ["code", "name", "symbol", "exchange_rate"],
                properties: [
                    new OA\Property(property: "code", type: "string", example: "USD"),
                    new OA\Property(property: "name", type: "string", example: "US Dollar"),
                    new OA\Property(property: "symbol", type: "string", example: "$"),
                    new OA\Property(property: "exchange_rate", type: "number", example: 0.00004),
                    new OA\Property(property: "is_default", type: "boolean"),
                    new OA\Property(property: "is_active", type: "boolean")
                ]
            )
        ),
        responses: [new OA\Response(response: 201, description: "Created")]
    )]
    public function store(StoreCurrencyRequest $request): JsonResponse
    {
        $this->authorize('create', Currency::class);
        
        $currency = $this->service->create($request->validated());
        return $this->successResponse($currency, 'Currency created', 201);
    }

    #[OA\Put(
        path: "/api/admin/currencies/{uuid}",
        summary: "Cập nhật tiền tệ",
        security: [['bearerAuth' => []]],
        tags: ["Currencies"],
        parameters: [new OA\Parameter(name: "uuid", in: "path", required: true, schema: new OA\Schema(type: "string"))],
        responses: [new OA\Response(response: 200, description: "Updated")]
    )]
    public function update(UpdateCurrencyRequest $request, string $uuid): JsonResponse
    {
        $currency = $this->service->findByUuidOrFail($uuid);
        $this->authorize('update', $currency);

        $currency = $this->service->update($uuid, $request->validated());
        return $this->successResponse($currency, 'Currency updated');
    }

    #[OA\Delete(
        path: "/api/admin/currencies/{uuid}",
        summary: "Xóa tiền tệ",
        security: [['bearerAuth' => []]],
        tags: ["Currencies"],
        parameters: [new OA\Parameter(name: "uuid", in: "path", required: true, schema: new OA\Schema(type: "string"))],
        responses: [
            new OA\Response(response: 200, description: "Deleted"),
            new OA\Response(response: 409, description: "Cannot delete default currency (Code: 409071)")
        ]
    )]
    public function destroy(string $uuid): JsonResponse
    {
        $currency = $this->service->findByUuidOrFail($uuid);
        $this->authorize('delete', $currency);
        
        $this->service->delete($uuid);
        
        return $this->successResponse(null, 'Currency deleted');
    }
}