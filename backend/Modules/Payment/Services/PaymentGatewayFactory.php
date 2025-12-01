<?php

namespace Modules\Payment\Services;

use InvalidArgumentException;

class PaymentGatewayFactory
{
    public function get(string $method)
    {
        return match ($method) {
            'cod' => null, // COD không cần gateway
            // 'vnpay' => new VnpayGateway(), // Sẽ implement sau
            // 'momo' => new MomoGateway(),
            default => throw new InvalidArgumentException("Payment method {$method} not supported"),
        };
    }
}