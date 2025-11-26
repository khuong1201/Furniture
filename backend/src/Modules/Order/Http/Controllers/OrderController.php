<?php

namespace Modules\Order\Http\Controllers;

use Modules\Shared\Http\Controllers\BaseController;
use Modules\Order\Services\OrderService;
use Modules\Order\Http\Requests\CreateOrderRequest;
use Modules\Order\Http\Requests\UpdateOrderRequest;

class OrderController extends BaseController
{
    public function __construct(OrderService $service)
    {
        parent::__construct($service);
    }

    protected function validateData($request): array
    {
        return match ($request->method()) {
            'POST' => app(CreateOrderRequest::class)->validated(),
            'PUT', 'PATCH' => app(UpdateOrderRequest::class)->validated(),
            default => $request->all(),
        };
    }
}
