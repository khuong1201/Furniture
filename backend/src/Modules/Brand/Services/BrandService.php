<?php

declare(strict_types=1);

namespace Modules\Brand\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Modules\Brand\Domain\Repositories\BrandRepositoryInterface;
use Modules\Shared\Contracts\StorageServiceInterface;
use Modules\Shared\Services\BaseService;
use Illuminate\Pagination\LengthAwarePaginator;

class BrandService extends BaseService
{
    public function __construct(
        BrandRepositoryInterface $repo,
        protected StorageServiceInterface $storageService
    ) {
        parent::__construct($repo);
    }

    public function create(array $data): Model
    {
        return DB::transaction(function () use ($data) {
            // Upload logo nếu có
            if (isset($data['logo']) && $data['logo'] instanceof \Illuminate\Http\UploadedFile) {
                $dto = $this->storageService->upload($data['logo'], 'brands');
                $data['logo_url'] = $dto->url;
                $data['public_id'] = $dto->publicId;
            }
            unset($data['logo']);

            return parent::create($data);
        });
    }

    public function update(string $uuid, array $data): Model
    {
        $brand = $this->findByUuidOrFail($uuid);

        return DB::transaction(function () use ($brand, $data) {
            if (isset($data['logo']) && $data['logo'] instanceof \Illuminate\Http\UploadedFile) {
                // Xóa ảnh cũ nếu cần
                if ($brand->public_id) {
                    $this->storageService->delete($brand->public_id);
                }
                
                $dto = $this->storageService->upload($data['logo'], 'brands');
                $data['logo_url'] = $dto->url;
                $data['public_id'] = $dto->publicId;
            }
            unset($data['logo']);

            $brand->update($data);
            return $brand;
        });
    }

    public function delete(string $uuid): bool
    {
        $brand = $this->findByUuidOrFail($uuid);
        // Có thể check logic chặn xóa nếu Brand đang có Product tại đây
        return $brand->delete();
    }
    
    public function filter(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        $filters['per_page'] = $perPage;
        return $this->repository->filter($filters);
    }
}