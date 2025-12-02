<?php

namespace Modules\Address\Services;

use Modules\Address\Domain\Repositories\AddressRepositoryInterface;
use Modules\Shared\Services\BaseService;
use Illuminate\Database\Eloquent\Model;

class AddressService extends BaseService
{
    public function __construct(AddressRepositoryInterface $repository)
    {
        parent::__construct($repository);
    }

    public function listForUser(int $userId)
    {
        return $this->repository->getAllByUser($userId);
    }

    protected function beforeCreate(array &$data): void
    {
        if (!empty($data['is_default']) && $data['is_default'] === true) {
            $this->repository->resetDefault($data['user_id']);
        }
        
        $count = $this->repository->getAllByUser($data['user_id'])->count();
        if ($count === 0) {
            $data['is_default'] = true;
        }
    }

    protected function beforeUpdate(Model $model, array &$data): void
    {
        if (!empty($data['is_default']) && $data['is_default'] === true) {
            $this->repository->resetDefault($model->user_id);
        }
    }
}