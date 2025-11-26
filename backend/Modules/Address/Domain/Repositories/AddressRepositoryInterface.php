<?php

namespace Modules\Address\Domain\Repositories;

use Modules\Address\Domain\Models\Address;
use Modules\Shared\Repositories\BaseRepositoryInterface;

interface AddressRepositoryInterface extends BaseRepositoryInterface
{
    public function getAllByUser($userId);
}
