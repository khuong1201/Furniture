<?php

namespace Modules\Warehouse\Services;

use Modules\Shared\Services\BaseService;
use Modules\Warehouse\Domain\Repositories\WarehouseRepositoryInterface;
use Illuminate\Support\Str;

class WarehouseService extends BaseService
{
    public function __construct(WarehouseRepositoryInterface $repository)
    {
        parent::__construct($repository);
    }

    public function create(array $data)
    {
        $data['uuid'] = $data['uuid'] ?? (string)Str::uuid();
        return parent::create($data);
    }
}
