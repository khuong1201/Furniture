<?php

namespace Modules\Collection\Services;

use Modules\Shared\Services\BaseService;
use Modules\Collection\Domain\Repositories\CollectionRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

class CollectionService extends BaseService
{
    public function __construct(CollectionRepositoryInterface $repository)
    {
        parent::__construct($repository);
    }

    public function create(array $data): Model
    {
        return DB::transaction(function () use ($data) {
            $collection = parent::create($data);

            if (!empty($data['product_ids'])) {
                $collection->products()->sync($data['product_ids']);
            }

            return $collection->load('products');
        });
    }

    public function update(string $uuid, array $data): Model
    {
        return DB::transaction(function () use ($uuid, $data) {
            $collection = $this->findByUuidOrFail($uuid);
            
            $collection->update($data);

            if (isset($data['product_ids'])) {
                $collection->products()->sync($data['product_ids']);
            }

            return $collection->load('products');
        });
    }
}