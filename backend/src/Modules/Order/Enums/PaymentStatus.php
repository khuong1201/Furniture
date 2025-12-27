<?php

declare(strict_types=1);

namespace Modules\Order\Enums;

enum PaymentStatus: string
{
    case UNPAID = 'unpaid';
    case PAID = 'paid';
    case REFUNDED = 'refunded';
    case FAILED = 'failed';
}