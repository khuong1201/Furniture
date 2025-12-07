<?php

declare(strict_types=1);

namespace Modules\Payment\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Modules\Shared\Http\Controllers\BaseController;
use Modules\Shared\Http\Resources\ApiResponse;
use Modules\Payment\Services\PaymentService;
use Modules\Payment\Http\Requests\StorePaymentRequest;
use Modules\Payment\Http\Requests\UpdatePaymentRequest;
use Modules\Payment\Domain\Models\Payment;
use OpenApi\Attributes as OA;

#[OA\Tag(name: "Payments", description: "API quản lý Thanh toán")]
class PaymentController extends BaseController
{
    public function __construct(PaymentService $service)
    {
        parent::__construct($service);
    }

    #[OA\Get(
        path: "/payments",
        summary: "Lịch sử giao dịch",
        security: [['bearerAuth' => []]],
        tags: ["Payments"],
        parameters: [
            new OA\Parameter(name: "page", in: "query", schema: new OA\Schema(type: "integer")),
            new OA\Parameter(name: "status", in: "query", schema: new OA\Schema(type: "string", enum: ["pending", "paid", "failed"])),
        ],
        responses: [ new OA\Response(response: 200, description: "Success") ]
    )]
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Payment::class);

        $filters = $request->all();
        $user = $request->user();

        if (!$user->hasPermissionTo('payment.view_all')) {
             $filters['user_id'] = $user->id;
        }

        $data = $this->service->paginate($request->integer('per_page', 15), $filters);
        return response()->json(ApiResponse::paginated($data));
    }

    #[OA\Post(
        path: "/payments",
        summary: "Tạo yêu cầu thanh toán (Initiate Payment)",
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
        responses: [ new OA\Response(response: 200, description: "Payment Initiated") ]
    )]
    public function store(StorePaymentRequest $request): JsonResponse
    {
        $this->authorize('create', Payment::class);

        $data = $request->validated();
        // User ID taken from token for security check in service
        
        $result = $this->service->initiatePayment($data);
        
        return response()->json(ApiResponse::success($result, 'Payment initiated', 201), 201);
    }

    #[OA\Get(
        path: "/payments/{uuid}",
        summary: "Xem chi tiết giao dịch",
        security: [['bearerAuth' => []]],
        tags: ["Payments"],
        parameters: [ new OA\Parameter(name: "uuid", in: "path", required: true, schema: new OA\Schema(type: "string")) ],
        responses: [ new OA\Response(response: 200, description: "Success") ]
    )]
    public function show(string $uuid): JsonResponse
    {
        $payment = $this->service->findByUuidOrFail($uuid);
        $this->authorize('view', $payment);
        return response()->json(ApiResponse::success($payment));
    }

    #[OA\Put(
        path: "/payments/{uuid}",
        summary: "Cập nhật trạng thái (Admin Only)",
        security: [['bearerAuth' => []]],
        tags: ["Payments"],
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "status", type: "string", enum: ["paid", "failed", "refunded"]),
                    new OA\Property(property: "transaction_id", type: "string"),
                ]
            )
        ),
        responses: [ new OA\Response(response: 200, description: "Updated") ]
    )]
    public function update(UpdatePaymentRequest $request, string $uuid): JsonResponse
    {
        $payment = $this->service->findByUuidOrFail($uuid);
        $this->authorize('update', $payment);

        $data = $this->service->update($uuid, $request->validated());
        
        return response()->json(ApiResponse::success($data, 'Payment updated successfully'));
    }

    #[OA\Post(
        path: "/payments/callback/{provider}",
        summary: "Webhook nhận kết quả thanh toán",
        tags: ["Payments"],
        parameters: [ new OA\Parameter(name: "provider", in: "path", schema: new OA\Schema(type: "string")) ],
        responses: [ new OA\Response(response: 200, description: "IPN Received") ]
    )]
    public function callback(Request $request, string $provider): JsonResponse
    {
        try {
            $this->service->processCallback($provider, $request->all());
            return response()->json(['message' => 'IPN received', 'status' => 200]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Payment Callback Failed [$provider]: " . $e->getMessage());
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }
}