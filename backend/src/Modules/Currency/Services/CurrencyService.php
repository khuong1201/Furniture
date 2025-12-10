<?php

declare(strict_types=1);

namespace Modules\Currency\Services;

use Modules\Shared\Services\BaseService;
use Modules\Currency\Domain\Repositories\CurrencyRepositoryInterface;
use Modules\Currency\Domain\Models\Currency;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class CurrencyService extends BaseService
{
    protected ?Currency $currentCurrency = null;
    protected ?Currency $baseCurrency = null;

    public function __construct(CurrencyRepositoryInterface $repository)
    {
        parent::__construct($repository);
        $this->detectCurrency();
    }

    public function detectCurrency(): void
    {
        if ($this->currentCurrency) return;

        $code = request()->header('X-Currency');
        
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
        return $this->currentCurrency;
    }

    /**
     * Quy đổi từ Integer (Base Unit) sang Float (Target Currency Unit).
     * Ví dụ: 100,000 VND -> 4.00 USD
     */
    public function convert(int|float|string $amount): float
    {
        $amount = (int) $amount; // Ép kiểu về số nguyên (Base Unit)

        if ($this->currentCurrency->code === $this->baseCurrency->code) {
            return (float) $amount;
        }

        return round($amount * $this->currentCurrency->exchange_rate, 2);
    }

    public function format(int|float|string $amount): string
    {
        $amount = (int) $amount; // Ép kiểu
        
        $converted = $this->convert($amount);
        $symbol = $this->currentCurrency->symbol;

        if ($this->currentCurrency->code === 'VND') {
            return number_format($converted, 0, ',', '.') . ' ' . $symbol;
        }

        return $symbol . number_format($converted, 2);
    }

    // --- CRUD Logic giữ nguyên như cũ ---
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
            throw ValidationException::withMessages(['uuid' => 'Cannot delete the default currency.']);
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