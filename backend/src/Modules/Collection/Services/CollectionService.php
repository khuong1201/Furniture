<?php

declare(strict_types=1);

namespace Modules\Collection\Services;

use Modules\Shared\Services\BaseService;
use Modules\Collection\Domain\Repositories\CollectionRepositoryInterface;
use Modules\Shared\Contracts\StorageServiceInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

class CollectionService extends BaseService
{
    public function __construct(
        CollectionRepositoryInterface $repository,
        protected StorageServiceInterface $storageService
    ) {
        parent::__construct($repository);
    }
    
    public function paginate(int $perPage = 15, array $filters = []): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        $filters['per_page'] = $perPage;
        return $this->repository->filter($filters);
    }

    public function create(array $data): Model
    {
        return DB::transaction(function () use ($data) {
            // Handle Banner Image Upload
            if (isset($data['banner_image']) && $data['banner_image'] instanceof \Illuminate\Http\UploadedFile) {
                $upload = $this->storageService->upload($data['banner_image'], 'collections');
                $data['banner_image'] = $upload['url'];
            }

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
            
            // Handle Banner Image Update
            if (isset($data['banner_image']) && $data['banner_image'] instanceof \Illuminate\Http\UploadedFile) {
                // Optional: Delete old image logic here if needed
                $upload = $this->storageService->upload($data['banner_image'], 'collections');
                $data['banner_image'] = $upload['url'];
            }
            
            $collection->update($data);

            if (isset($data['product_ids'])) {
                $collection->products()->sync($data['product_ids']);
            }

            return $collection->load('products');
        });
    }
}