<?php

declare(strict_types=1);

namespace Modules\Currency\Domain\Repositories;

use Modules\Shared\Contracts\BaseRepositoryInterface;
use Modules\Currency\Domain\Models\Currency;
use Illuminate\Database\Eloquent\Collection;

interface CurrencyRepositoryInterface extends BaseRepositoryInterface
{
    public function getActiveCurrencies(): Collection;
    public function findByCode(string $code): ?Currency;
    public function getDefaultCurrency(): ?Currency;
}