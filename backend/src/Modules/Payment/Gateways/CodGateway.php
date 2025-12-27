<?php

declare(strict_types=1);

namespace Modules\Payment\Gateways;

use Modules\Payment\Contracts\PaymentGatewayInterface;

class CodGateway implements PaymentGatewayInterface
{
    public function createPaymentUrl(string $paymentUuid, float $amount, string $orderInfo = ''): string
    {
        return ""; 
    }

    public function verifyWebhook(array $payload): ?array
    {
        return null;
    }
}