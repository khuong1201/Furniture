<?php

declare(strict_types=1);

namespace Modules\Payment\Events;

use Modules\Payment\Domain\Models\Payment;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PaymentCompleted
{
    use Dispatchable, SerializesModels;

    public function __construct(public Payment $payment) {}
}