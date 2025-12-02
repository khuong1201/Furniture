<?php

namespace Modules\Payment\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Modules\Shared\Http\Controllers\BaseController;
use Modules\Shared\Http\Resources\ApiResponse;
use Modules\Payment\Services\PaymentService;
use Modules\Payment\Http\Requests\StorePaymentRequest;
use Modules\Payment\Domain\Models\Payment;

class PaymentController extends BaseController
{
    public function __construct(PaymentService $service)
    {
        parent::__construct($service);
    }

    public function index(Request $request): JsonResponse
    {
        $filters = $request->all();
        if (!auth()->user()->hasRole('admin')) {
             $filters['user_id'] = auth()->id();
        }

        $data = $this->service->paginate($request->get('per_page', 15), $filters);
        return response()->json(ApiResponse::paginated($data));
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', Payment::class);

        $validatedRequest = app(StorePaymentRequest::class);
        
        $result = $this->service->create($validatedRequest->validated());
        
        return response()->json(ApiResponse::success($result, 'Payment initiated', 201), 201);
    }

    public function show(string $uuid): JsonResponse
    {
        $payment = $this->service->getRepository()->findByUuid($uuid);
        if (!$payment) {
            return response()->json(ApiResponse::error('Payment not found', 404), 404);
        }

        $this->authorize('view', $payment);

        return response()->json(ApiResponse::success($payment));
    }

    public function callback(Request $request, string $provider): JsonResponse
    {
        try {
            $this->service->processCallback($provider, $request->all());
            return response()->json(['message' => 'IPN received']);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }
}