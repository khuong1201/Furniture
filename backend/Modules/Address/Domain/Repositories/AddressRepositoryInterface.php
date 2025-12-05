<?php

declare(strict_types=1);

namespace Modules\Address\Domain\Repositories;

use Modules\Shared\Repositories\BaseRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

interface AddressRepositoryInterface extends BaseRepositoryInterface
{
    public function getAllByUser(int $userId): Collection;
    public function resetDefault(int $userId): void;
}