<?php

declare(strict_types=1);

namespace Modules\Currency\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Modules\Shared\Http\Controllers\BaseController;
use Modules\Shared\Http\Resources\ApiResponse;
use Modules\Currency\Services\CurrencyService;
use Modules\Currency\Http\Requests\StoreCurrencyRequest;
use Modules\Currency\Http\Requests\UpdateCurrencyRequest;
use Modules\Currency\Domain\Models\Currency;
use OpenApi\Attributes as OA;

#[OA\Tag(name: "Currencies", description: "API quản lý Tiền tệ")]
class CurrencyController extends BaseController
{
    public function __construct(CurrencyService $service)
    {
        parent::__construct($service);
    }

    #[OA\Get(
        path: "/public/currencies",
        summary: "Lấy danh sách tiền tệ hỗ trợ (Public)",
        tags: ["Currencies"],
        responses: [new OA\Response(response: 200, description: "Success")]
    )]
    public function getActive(): JsonResponse
    {
        // Public API để frontend làm dropdown chọn tiền tệ
        $currencies = $this->service->getRepository()->getActiveCurrencies();
        return response()->json(ApiResponse::success($currencies));
    }

    #[OA\Get(
        path: "/admin/currencies",
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
        // Giả sử đã có policy, nếu chưa có thì tạo CurrencyPolicy tương tự các module khác
        
        $data = $this->service->paginate($request->integer('per_page', 15), $request->all());
        return response()->json(ApiResponse::paginated($data));
    }

    #[OA\Post(
        path: "/admin/currencies",
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
                    new OA\Property(property: "exchange_rate", type: "number", format: "float", example: 0.00004),
                    new OA\Property(property: "is_default", type: "boolean"),
                    new OA\Property(property: "is_active", type: "boolean")
                ]
            )
        ),
        responses: [new OA\Response(response: 201, description: "Created")]
    )]
    public function store(StoreCurrencyRequest $request): JsonResponse
    {
        // $this->authorize('create', Currency::class);
        $currency = $this->service->create($request->validated());
        return response()->json(ApiResponse::success($currency, 'Currency created', 201), 201);
    }

    #[OA\Put(
        path: "/admin/currencies/{uuid}",
        summary: "Cập nhật tiền tệ",
        security: [['bearerAuth' => []]],
        tags: ["Currencies"],
        parameters: [new OA\Parameter(name: "uuid", in: "path", required: true, schema: new OA\Schema(type: "string"))],
        requestBody: new OA\RequestBody(content: new OA\JsonContent(properties: [
            new OA\Property(property: "exchange_rate", type: "number"),
            new OA\Property(property: "is_default", type: "boolean"),
            new OA\Property(property: "is_active", type: "boolean")
        ])),
        responses: [new OA\Response(response: 200, description: "Updated")]
    )]
    public function update(UpdateCurrencyRequest $request, string $uuid): JsonResponse
    {
        // $this->authorize('update', Currency::class);
        $currency = $this->service->update($uuid, $request->validated());
        return response()->json(ApiResponse::success($currency, 'Currency updated'));
    }

    #[OA\Delete(
        path: "/admin/currencies/{uuid}",
        summary: "Xóa tiền tệ",
        security: [['bearerAuth' => []]],
        tags: ["Currencies"],
        parameters: [new OA\Parameter(name: "uuid", in: "path", required: true, schema: new OA\Schema(type: "string"))],
        responses: [new OA\Response(response: 200, description: "Deleted")]
    )]
    public function destroy(string $uuid): JsonResponse
    {
        // $this->authorize('delete', Currency::class);
        $this->service->delete($uuid);
        return response()->json(ApiResponse::success(null, 'Currency deleted'));
    }
}