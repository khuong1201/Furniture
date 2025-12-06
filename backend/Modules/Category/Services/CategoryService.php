<?php

declare(strict_types=1);

namespace Modules\Category\Services;

use Modules\Shared\Services\BaseService;
use Modules\Category\Domain\Repositories\CategoryRepositoryInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Collection;

class CategoryService extends BaseService
{
    public function __construct(CategoryRepositoryInterface $repository)
    {
        parent::__construct($repository);
    }

    public function getTree(): Collection
    {
        return $this->repository->getTree();
    }
    
    public function paginate(int $perPage = 15, array $filters = []): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        $filters['per_page'] = $perPage;
        return $this->repository->filter($filters);
    }

    protected function beforeUpdate(Model $model, array &$data): void
    {
        if (!empty($data['parent_id'])) {
            if ($data['parent_id'] == $model->id) {
                throw ValidationException::withMessages(['parent_id' => 'Danh mục không thể là cha của chính nó.']);
            }

            if ($this->isDescendant($model->id, (int)$data['parent_id'])) {
                throw ValidationException::withMessages(['parent_id' => 'Không thể gán danh mục con làm danh mục cha (Vòng lặp vô hạn).']);
            }
        }
    }

    protected function isDescendant(int|string $categoryId, int|string $targetId): bool
    {
        $category = $this->repository->findById($categoryId);
        if (!$category) return false;
        
        $category->load('allChildren'); 
        
        return $this->checkIdInTree($category->allChildren, $targetId);
    }
    
    private function checkIdInTree(Collection $nodes, int|string $targetId): bool 
    {
        foreach ($nodes as $node) {
            if ($node->id == $targetId) return true;
            if ($node->allChildren->isNotEmpty()) {
                if ($this->checkIdInTree($node->allChildren, $targetId)) return true;
            }
        }
        return false;
    }
}