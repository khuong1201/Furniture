<?php

declare(strict_types=1);

namespace Modules\Warehouse\Services;

use Modules\Shared\Services\BaseService;
use Modules\Warehouse\Domain\Repositories\WarehouseRepositoryInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class WarehouseService extends BaseService
{
    public function __construct(WarehouseRepositoryInterface $repository)
    {
        parent::__construct($repository);
    }

    public function paginate(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        $filters['per_page'] = $perPage;
        return $this->repository->filter($filters);
    }

    protected function beforeDelete(Model $model): void
    {
        // Check tồn kho trước khi xóa
        if ($this->repository->hasStock($model->id)) {
            throw ValidationException::withMessages([
                'warehouse' => ['Không thể xóa kho đang chứa hàng tồn (Quantity > 0). Vui lòng chuyển kho hoặc xuất hết hàng trước.']
            ]);
        }
    }
}