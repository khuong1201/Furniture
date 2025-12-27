<?php

namespace Modules\Product\Listeners;

use Modules\Product\Events\ProductCreated;
use Modules\Product\Mails\NewProductMail;
use Modules\Shared\Services\MailService; 
use Illuminate\Contracts\Queue\ShouldQueue;

class SendNewProductEmail implements ShouldQueue
{
    public $queue = 'default';

    public function __construct(protected MailService $mailService) {}

    public function handle(ProductCreated $event): void
    {
        $product = $event->product;
        $adminEmail = 'admin@system.com';

        $this->mailService->sendQueue($adminEmail, new NewProductMail($product));
    }
}