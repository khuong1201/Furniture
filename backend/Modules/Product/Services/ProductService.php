<?php

namespace Modules\Product\Services;

use Modules\Shared\Services\BaseService;
use Modules\Product\Domain\Repositories\ProductRepositoryInterface;
use Modules\Product\Domain\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

class ProductService extends BaseService
{
    public function __construct(
        ProductRepositoryInterface $repo,
        protected ProductImageService $imageService
    ) {
        parent::__construct($repo);
    }

    public function create(array $data): Model
    {
        return DB::transaction(function () use ($data) {
            $product = parent::create($data); 

            if (!empty($data['images']) && is_array($data['images'])) {
                $this->imageService->uploadMultiple($product, $data['images']);
            }

            if (!empty($data['warehouse_stock'])) {
                $this->syncStock($product, $data['warehouse_stock']);
            }

            return $product->load(['images', 'warehouses']);
        });
    }

    public function update(string $uuid, array $data): Model
    {
        return DB::transaction(function () use ($uuid, $data) {
            $product = parent::update($uuid, $data);

            if (!empty($data['images']) && is_array($data['images'])) {
                $this->imageService->uploadMultiple($product, $data['images']);
            }

            if (isset($data['warehouse_stock'])) {
                $this->syncStock($product, $data['warehouse_stock']);
            }

            return $product->load(['images', 'warehouses']);
        });
    }

    protected function syncStock(Product $product, array $stockData): void
    {
        $syncData = [];
        foreach ($stockData as $item) {
            $syncData[$item['warehouse_id']] = ['quantity' => $item['quantity']];
        }
        $product->warehouses()->sync($syncData);
    }
    
    public function paginate(int $perPage = 15, array $filters = []): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        $filters['per_page'] = $perPage;
        return $this->repository->filter($filters);
    }
}