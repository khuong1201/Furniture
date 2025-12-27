<?php

declare(strict_types=1);

namespace Modules\Payment\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Modules\Shared\Http\Controllers\BaseController;
use Modules\Payment\Services\PaymentService;
use Modules\Payment\Http\Requests\StorePaymentRequest;
use Modules\Payment\Domain\Models\Payment;
use Modules\Payment\Http\Resources\PaymentResource;
use OpenApi\Attributes as OA;

#[OA\Tag(name: "Payments", description: "API quản lý Thanh toán")]
class PaymentController extends BaseController
{
    public function __construct(PaymentService $service)
    {
        parent::__construct($service);
    }

    #[OA\Get(
        path: "/api/payments",
        summary: "Danh sách lịch sử thanh toán",
        security: [['bearerAuth' => []]],
        tags: ["Payments"],
        parameters: [
            new OA\Parameter(name: "page", in: "query", schema: new OA\Schema(type: "integer")),
            new OA\Parameter(name: "per_page", in: "query", schema: new OA\Schema(type: "integer")),
            new OA\Parameter(name: "status", in: "query", schema: new OA\Schema(type: "string", enum: ["paid", "pending", "failed"])),
            new OA\Parameter(name: "search", in: "query", description: "Tìm theo TransactionID hoặc OrderCode", schema: new OA\Schema(type: "string")),
        ],
        responses: [
            new OA\Response(
                response: 200, 
                description: "Success",
                content: new OA\JsonContent(properties: [
                    new OA\Property(property: "success", type: "boolean", example: true),
                    new OA\Property(property: "data", type: "array", items: new OA\Items(ref: "#/components/schemas/PaymentResource")),
                    new OA\Property(property: "meta", type: "object"),
                    new OA\Property(property: "links", type: "object")
                ])
            )
        ]
    )]
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Payment::class);

        $filters = $request->all();

        $perPage = $request->integer('per_page', 15);
        $filters['per_page'] = $perPage;

        $paginator = $this->service->filter($perPage, $filters);

        $paginator->through(fn($payment) => new PaymentResource($payment));

        return $this->successResponse($paginator);
    }

    #[OA\Post(
        path: "/api/payments",
        summary: "Tạo thanh toán",
        description: "Khởi tạo giao dịch. Trả về link redirect nếu là thanh toán Online.",
        security: [['bearerAuth' => []]],
        tags: ["Payments"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["order_uuid", "method"],
                properties: [
                    new OA\Property(property: "order_uuid", type: "string", format: "uuid"),
                    new OA\Property(property: "method", type: "string", enum: ["cod", "momo", "vnpay"]),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200, 
                description: "Success",
                content: new OA\JsonContent(properties: [
                    new OA\Property(property: "success", type: "boolean", example: true),
                    new OA\Property(property: "data", type: "object", properties: [
                        new OA\Property(property: "payment_uuid", type: "string"),
                        new OA\Property(property: "redirect_url", type: "string", nullable: true),
                        new OA\Property(property: "is_cod", type: "boolean")
                    ])
                ])
            ),
            new OA\Response(response: 409, description: "Order already paid"),
            new OA\Response(response: 400, description: "Method not supported")
        ]
    )]
    public function store(StorePaymentRequest $request): JsonResponse
    {
        $this->authorize('create', Payment::class);

        // Store trả về array cấu trúc init payment, không phải model Payment đầy đủ
        // nên ta giữ nguyên successResponse với array kết quả từ service
        $result = $this->service->initiatePayment($request->validated());
        
        return $this->successResponse($result, 'Payment initiated', 201);
    }
    
    #[OA\Patch(
        path: "/api/payments/{uuid}/status",
        summary: "Cập nhật trạng thái thanh toán (Admin)",
        security: [['bearerAuth' => []]],
        tags: ["Payments"],
        parameters: [ new OA\Parameter(name: "uuid", in: "path", required: true, schema: new OA\Schema(type: "string")) ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(properties: [
                new OA\Property(property: "status", type: "string", enum: ["paid", "pending", "failed"])
            ])
        ),
        responses: [ new OA\Response(response: 200, description: "Success") ]
    )]
    public function updateStatus(Request $request, string $uuid): JsonResponse
    {
        $payment = $this->service->findByUuidOrFail($uuid);

        $this->authorize('update', $payment);

        $request->validate([
            'status' => 'required|string|in:paid,pending,failed,refunded'
        ]);

        $payment = $this->service->updateStatus($uuid, $request->status);

        return $this->successResponse(new PaymentResource($payment), 'Payment status updated');
    }

    #[OA\Post(
        path: "/api/payments/callback/{provider}",
        summary: "Webhook nhận kết quả (IPN)",
        tags: ["Payments"],
        parameters: [ 
            new OA\Parameter(name: "provider", in: "path", schema: new OA\Schema(type: "string", enum: ["momo", "vnpay"])) 
        ],
        responses: [ 
            new OA\Response(response: 204, description: "Processed"),
            new OA\Response(response: 400, description: "Invalid Signature")
        ]
    )]
    public function callback(Request $request, string $provider): JsonResponse
    {
        try {
            $this->service->processCallback($provider, $request->all());
            return response()->json([], 204);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    #[OA\Get(
        path: "/api/payments/{uuid}",
        summary: "Chi tiết giao dịch",
        security: [['bearerAuth' => []]],
        tags: ["Payments"],
        parameters: [ new OA\Parameter(name: "uuid", in: "path", required: true, schema: new OA\Schema(type: "string")) ],
        responses: [ 
            new OA\Response(
                response: 200, 
                description: "Success",
                content: new OA\JsonContent(ref: "#/components/schemas/PaymentResource")
            ) 
        ]
    )]
    public function show(string $uuid): JsonResponse
    {
        $payment = $this->service->findByUuidOrFail($uuid);
        
        $this->authorize('view', $payment);

        return $this->successResponse(new PaymentResource($payment));
    }
}