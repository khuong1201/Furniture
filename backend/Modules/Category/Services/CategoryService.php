<?php

namespace Modules\Category\Services;

use Modules\Shared\Services\BaseService;
use Modules\Category\Domain\Repositories\CategoryRepositoryInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class CategoryService extends BaseService
{
    public function __construct(CategoryRepositoryInterface $repository)
    {
        parent::__construct($repository);
    }

    public function getTree()
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
        if (isset($data['parent_id']) && $data['parent_id']) {
            if ($data['parent_id'] == $model->id) {
                throw ValidationException::withMessages(['parent_id' => 'A category cannot be its own parent.']);
            }

            if ($this->isDescendant($model->id, $data['parent_id'])) {
                throw ValidationException::withMessages(['parent_id' => 'Cannot assign a descendant category as parent.']);
            }
        }
    }

    protected function isDescendant($categoryId, $targetId): bool
    {
        $category = $this->repository->findById($categoryId);
        $children = $category->allChildren;
        
        return $this->checkIdInTree($children, $targetId);
    }
    
    private function checkIdInTree($nodes, $targetId): bool 
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