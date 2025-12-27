<?php

declare(strict_types=1);

namespace Modules\Currency\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Modules\Currency\Domain\Models\Currency;
use Modules\Currency\Domain\Repositories\CurrencyRepositoryInterface;
use Modules\Shared\Exceptions\BusinessException; // <--- Dùng cái này
use Modules\Shared\Services\BaseService;

class CurrencyService extends BaseService
{
    protected ?Currency $currentCurrency = null;
    protected ?Currency $baseCurrency = null;

    public function __construct(CurrencyRepositoryInterface $repository)
    {
        parent::__construct($repository);
    }

    public function setCurrency(?string $code): void
    {
        $currencies = Cache::remember('active_currencies', 86400, function () {
            return $this->repository->getActiveCurrencies()->keyBy('code');
        });

        $this->baseCurrency = $currencies->firstWhere('is_default', true);

        if ($code && $currencies->has(strtoupper($code))) {
            $this->currentCurrency = $currencies->get(strtoupper($code));
        } else {
            $this->currentCurrency = $this->baseCurrency;
        }
    }

    public function getCurrentCurrency(): Currency
    {
        if (!$this->currentCurrency) {
            $this->setCurrency(null);
        }
        return $this->currentCurrency;
    }

    public function convert(int|float|string $amount): float
    {
        $currency = $this->getCurrentCurrency();
        $amount = (float) $amount; 

        if ($currency->code === $this->baseCurrency?->code) {
            return $amount;
        }

        return round($amount * $currency->exchange_rate, 2);
    }

    public function format(int|float|string $amount): string
    {
        $converted = $this->convert($amount);
        $currency = $this->getCurrentCurrency();
        $symbol = $currency->symbol;

        if ($currency->code === 'VND') {
            return number_format($converted, 0, ',', '.') . ' ' . $symbol;
        }

        return $symbol . number_format($converted, 2);
    }

    public function create(array $data): Model
    {
        return DB::transaction(function () use ($data) {
            if (!empty($data['is_default']) && $data['is_default']) {
                $this->repository->query()->update(['is_default' => false]);
            }
            $currency = parent::create($data);
            $this->clearCache();
            return $currency;
        });
    }

    public function update(string $uuid, array $data): Model
    {
        return DB::transaction(function () use ($uuid, $data) {
            if (!empty($data['is_default']) && $data['is_default']) {
                $this->repository->query()->update(['is_default' => false]);
            }
            $currency = parent::update($uuid, $data);
            $this->clearCache();
            return $currency;
        });
    }

    public function delete(string $uuid): bool
    {
        $currency = $this->findByUuidOrFail($uuid);

        if ($currency->is_default) {
            throw new BusinessException(409071); 
        }

        $res = parent::delete($uuid);
        $this->clearCache();
        return $res;
    }

    protected function clearCache(): void
    {
        Cache::forget('active_currencies');
    }
}