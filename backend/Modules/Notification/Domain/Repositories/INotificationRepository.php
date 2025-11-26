<?php

namespace Modules\Notification\Domain\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Modules\Notification\Domain\Models\Notification;

interface INotificationRepository
{
    public function all($withTrashed = false);
    public function paginate($perPage = 15): LengthAwarePaginator;
    public function findById($id): Notification;
    public function findByUuid(string $uuid): Notification;
    public function create(array $data): Notification;
    public function update(Notification $notification, array $data): Notification;
    public function delete(Notification $notification): bool;
}
