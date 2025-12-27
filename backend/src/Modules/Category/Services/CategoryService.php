<?php

declare(strict_types=1);

namespace Modules\Category\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Http\UploadedFile;
use Modules\Category\Domain\Repositories\CategoryRepositoryInterface;
use Modules\Shared\Contracts\StorageServiceInterface; 
use Modules\Shared\Exceptions\BusinessException;
use Modules\Shared\Services\BaseService;

class CategoryService extends BaseService
{
    public function __construct(
        CategoryRepositoryInterface $repository,
        protected StorageServiceInterface $storageService
    ) {
        parent::__construct($repository);
    }

    /**
     * Override hàm paginate của BaseService.
     * Nhận vào array filters (từ request) thay vì chỉ int perPage.
     */
    public function paginate(mixed $params = 15): mixed
    {
        // Trường hợp 1: Truyền vào Array (Filters từ Controller)
        if (is_array($params)) {
            // Gọi hàm filter custom bên Repository
            if (method_exists($this->repository, 'filter')) {
                return $this->repository->filter($params);
            }
            
            // Fallback: Nếu repo không có hàm filter, lấy per_page từ mảng
            $perPage = $params['per_page'] ?? 15;
            return parent::paginate((int)$perPage);
        }

        // Trường hợp 2: Truyền vào Int (Mặc định của BaseService)
        return parent::paginate((int)$params);
    }

    public function getTree(): Collection
    {
        return $this->repository->getTree();
    }

    public function create(array $data): Model
    {
        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }
        if ($this->repository->findBySlug($data['slug'])) {
            throw new BusinessException(409051, "Category slug '{$data['slug']}' already exists");
        }

        $uuid = (string) Str::uuid();
        $data['uuid'] = $uuid; 

        if (isset($data['image']) && $data['image'] instanceof UploadedFile) {
            $dto = $this->storageService->upload($data['image'], 'categories/' . $uuid);
            $data['image'] = $dto->url;
        }

        return parent::create($data);
    }

    public function update(string $uuid, array $data): Model
    {
        $category = $this->findByUuidOrFail($uuid);

        return DB::transaction(function () use ($category, $data, $uuid) {
            if (!empty($data['parent_id'])) {
                $parentId = (int) $data['parent_id'];

                if ($parentId === $category->id) {
                    throw new BusinessException(422052, 'A category cannot be its own parent.');
                }

                if ($this->isDescendant($category, $parentId)) {
                    throw new BusinessException(422052, 'Cannot assign a descendant category as parent.');
                }
            }

            if (isset($data['name']) && empty($data['slug'])) {
                $data['slug'] = Str::slug($data['name']);
            }

            if (isset($data['slug'])) {
                $exists = $this->repository->findBySlug($data['slug']);
                if ($exists && $exists->id !== $category->id) {
                    throw new BusinessException(409051);
                }
            }
            if (isset($data['image']) && $data['image'] instanceof UploadedFile) {
                $dto = $this->storageService->upload($data['image'], 'categories/' . $uuid);
                $data['image'] = $dto->url;
            }

            $category->update($data);
            return $category;
        });
    }

    protected function isDescendant(Model $currentCategory, int $targetId): bool
    {
        $currentCategory->load('allChildren');
        $descendantIds = $this->flattenChildren($currentCategory->allChildren);
        return in_array($targetId, $descendantIds);
    }

    protected function flattenChildren(Collection $children): array
    {
        $ids = [];
        foreach ($children as $child) {
            $ids[] = $child->id;
            if ($child->allChildren->isNotEmpty()) {
                $ids = array_merge($ids, $this->flattenChildren($child->allChildren));
            }
        }
        return $ids;
    }
}