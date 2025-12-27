<?php

declare(strict_types=1);

namespace Modules\Payment\Gateways;

use Modules\Payment\Contracts\PaymentGatewayInterface;
use Illuminate\Support\Facades\Log;

class MomoGateway implements PaymentGatewayInterface
{
    public function createPaymentUrl(string $paymentUuid, float $amount, string $orderInfo = ''): string
    {
        return "https://test-payment.momo.vn/pay?id=" . $paymentUuid; 
    }

    public function verifyWebhook(array $payload): ?array
    {
        if (!isset($payload['resultCode'])) return null;

        return [
            'status' => $payload['resultCode'] == 0 ? 'paid' : 'failed',
            'transaction_id' => $payload['transId'] ?? null,
            'message' => $payload['message'] ?? ''
        ];
    }
}