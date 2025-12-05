<?php

declare(strict_types=1);

namespace Modules\Currency\Infrastructure\Repositories;

use Modules\Shared\Repositories\EloquentBaseRepository;
use Modules\Currency\Domain\Repositories\CurrencyRepositoryInterface;
use Modules\Currency\Domain\Models\Currency;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class EloquentCurrencyRepository extends EloquentBaseRepository implements CurrencyRepositoryInterface
{
    public function __construct(Currency $model)
    {
        parent::__construct($model);
    }

    public function getActiveCurrencies(): Collection
    {
        return $this->model->active()->orderBy('is_default', 'desc')->get();
    }

    public function findByCode(string $code): ?Currency
    {
        return $this->model->where('code', strtoupper($code))->first();
    }

    public function getDefaultCurrency(): ?Currency
    {
        return $this->model->where('is_default', true)->first();
    }

    public function filter(array $filters): LengthAwarePaginator
    {
        $query = $this->query();

        if (!empty($filters['search'])) {
            $query->where('name', 'like', "%{$filters['search']}%")
                  ->orWhere('code', 'like', "%{$filters['search']}%");
        }

        return $query->latest()->paginate($filters['per_page'] ?? 15);
    }
}