<?php

declare(strict_types=1);

namespace Modules\Payment\Services;

use Modules\Payment\Contracts\PaymentGatewayInterface;
use InvalidArgumentException;

class PaymentGatewayFactory
{
    public function get(string $method): ?PaymentGatewayInterface
    {
        return match ($method) {
            'cod' => null, // COD xử lý nội bộ, không cần Gateway class
            // 'vnpay' => app(VnpayGateway::class), 
            // 'momo' => app(MomoGateway::class),
            default => throw new InvalidArgumentException("Payment method {$method} not supported"),
        };
    }
}