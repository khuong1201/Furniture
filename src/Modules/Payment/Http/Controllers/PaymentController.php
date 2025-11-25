<?php

namespace Modules\Payment\Http\Controllers;

use Illuminate\Routing\Controller;
use Modules\Payment\Services\PaymentService;
use Modules\Payment\Http\Requests\StorePaymentRequest;
use Modules\Payment\Http\Requests\UpdatePaymentRequest;

class PaymentController extends Controller
{
    protected PaymentService $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    public function index()
    {
        return response()->json($this->paymentService->all());
    }

    public function store(StorePaymentRequest $request)
    {
        $payment = $this->paymentService->create($request->validated());
        return response()->json($payment, 201);
    }

    public function update(UpdatePaymentRequest $request, string $uuid)
    {
        $payment = $this->paymentService->update($uuid, $request->validated());
        return response()->json($payment);
    }

    public function destroy(string $uuid)
    {
        $this->paymentService->delete($uuid);
        return response()->json(['message' => 'Payment deleted successfully.']);
    }
}
