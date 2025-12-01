<?php

namespace Modules\Log\Domain\Repositories;

use Modules\Shared\Repositories\BaseRepositoryInterface;
use Modules\Log\Domain\Models\Log;

interface LogRepositoryInterface extends BaseRepositoryInterface
{
    public function filter(array $filters);
}