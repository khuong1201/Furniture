<?php

namespace Modules\Review\Services;

use Modules\Review\Domain\Repositories\ReviewRepositoryInterface;
use Illuminate\Support\Facades\Auth;

class ReviewService
{
    public function __construct(protected ReviewRepositoryInterface $repo) {}

    public function list(array $filters = [], int $perPage = 10)
    {
        return $this->repo->paginate($perPage, $filters);
    }

    public function create(array $data)
    {
        $data['user_id'] = Auth::id();
        return $this->repo->create($data);
    }

    public function update(string $uuid, array $data)
    {
        $review = $this->repo->findByUuid($uuid);
        return $this->repo->update($review, $data);
    }

    public function delete(string $uuid)
    {
        $review = $this->repo->findByUuid($uuid);
        return $this->repo->delete($review);
    }
}
