<?php

namespace Modules\Payment\Http\Controllers;

use Illuminate\Http\Request;
use Modules\Shared\Http\Controllers\BaseController;
use Modules\Shared\Http\Resources\ApiResponse;
use Modules\Payment\Services\PaymentService;
use Modules\Payment\Http\Requests\StorePaymentRequest;

class PaymentController extends BaseController
{
    public function __construct(PaymentService $service)
    {
        parent::__construct($service);
    }

    public function store(Request $request): \Illuminate\Http\JsonResponse
    {
         $validatedRequest = app(StorePaymentRequest::class);
        $result = $this->service->create($request->validated());
        return response()->json(ApiResponse::success($result, 'Payment initiated', 201), 201);
    }
}