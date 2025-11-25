<?php

namespace Modules\Product\Services;

use Modules\Shared\Services\BaseService;
use Modules\Product\Domain\Repositories\ProductRepositoryInterface;
use Modules\Product\Services\ProductImageService;
use Modules\Product\Domain\Models\Product;
use Illuminate\Support\Str;

class ProductService extends BaseService
{
    protected ProductImageService $imageService;

    public function __construct(ProductRepositoryInterface $repository, ProductImageService $imageService)
    {
        parent::__construct($repository);
        $this->imageService = $imageService;
    }

    public function list(array $filters = [])
    {
        return method_exists($this->repository, 'filter') 
            ? $this->repository->filter($filters) 
            : $this->repository->all();
    }

    public function create(array $data, array $images = []): Product
    {
        $data['uuid'] = $data['uuid'] ?? Str::uuid()->toString();

        $product = parent::create($data);

        if (!empty($images)) {
            $this->imageService->uploadImages($product, $images);
        }
        
        if (!empty($data['warehouse_stock'])) {
            $attach = [];
            foreach ($data['warehouse_stock'] as $stock) {
                $attach[$stock['warehouse_id']] = ['quantity' => $stock['quantity']];
            }
            $product->warehouses()->sync($attach);
        }

        return $product->load('images', 'category', 'warehouses');
    }

    public function update(string $uuid, array $data, array $images = [])
    {
        $product = $this->findByUuid($uuid);

        if (!empty($data)) {
            $product->update($data);
        }

        if (!empty($images)) {
            $this->imageService->uploadImages($product, $images);
        }

        if (isset($data['warehouse_stock'])) {
            $attach = [];
            foreach ($data['warehouse_stock'] as $stock) {
                $attach[$stock['warehouse_id']] = ['quantity' => $stock['quantity']];
            }
            $product->warehouses()->sync($attach);
        }

        $product->refresh();

        return $product->load('images', 'category', 'warehouses');
    }

    public function getProductWithStock(string $uuid, bool $detailed = false)
    {
        $product = $this->findByUuid($uuid);

        if ($detailed) {
            $product->load(['warehouses' => function($q) {
                $q->select('warehouses.id', 'name')->withPivot('quantity');
            }]);
        } else {
            $product->total_quantity = $product->warehouses()->sum('warehouse_product.quantity');
        }

        return $product->load('images', 'category');
    }

}
