<?php

declare(strict_types=1);

namespace Modules\Payment\Contracts;

interface PaymentGatewayInterface
{
    public function createPaymentUrl(string $paymentUuid, float $amount, string $orderInfo = ''): string;

    public function verifyWebhook(array $payload): ?array;
}