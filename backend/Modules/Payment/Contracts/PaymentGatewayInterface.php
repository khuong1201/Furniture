<?php

namespace Modules\Payment\Contracts;

interface PaymentGatewayInterface
{
    public function createPaymentUrl(string $orderUuid, float $amount): string;
    public function verifyWebhook(array $payload): bool;
}