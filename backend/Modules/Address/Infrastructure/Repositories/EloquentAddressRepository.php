<?php

declare(strict_types=1);

namespace Modules\Address\Infrastructure\Repositories;

use Modules\Shared\Repositories\EloquentBaseRepository;
use Modules\Address\Domain\Repositories\AddressRepositoryInterface;
use Modules\Address\Domain\Models\Address;
use Illuminate\Database\Eloquent\Collection;

class EloquentAddressRepository extends EloquentBaseRepository implements AddressRepositoryInterface
{
    public function __construct(Address $model)
    {
        parent::__construct($model);
    }
    
    public function getAllByUser(int $userId): Collection
    {
        return $this->model
            ->where('user_id', $userId)
            ->orderByDesc('is_default') 
            ->latest()
            ->get();
    }

    public function resetDefault(int $userId): void
    {
        $this->model
            ->where('user_id', $userId)
            ->where('is_default', true)
            ->update(['is_default' => false]);
    }
}