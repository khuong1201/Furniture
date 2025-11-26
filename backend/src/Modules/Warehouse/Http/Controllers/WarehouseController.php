<?php

namespace Modules\Warehouse\Http\Controllers;

use Modules\Shared\Http\Controllers\BaseController;
use Modules\Warehouse\Services\WarehouseService;
use Modules\Warehouse\Http\Requests\StoreWarehouseRequest;
use Modules\Warehouse\Http\Requests\UpdateWarehouseRequest;

class WarehouseController extends BaseController
{
    public function __construct(WarehouseService $service)
    {
        parent::__construct($service);
        $this->middleware(\Modules\Auth\Http\Middleware\JwtAuthenticate::class);
    }

    protected function validateData($request): array
    {
        return match ($request->method()) {
            'POST' => app(StoreWarehouseRequest::class)->validated(),
            'PUT', 'PATCH' => app(UpdateWarehouseRequest::class)->validated(),
            default => $request->all(),
        };
    }
}
