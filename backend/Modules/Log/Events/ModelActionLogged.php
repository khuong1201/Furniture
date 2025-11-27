<?php
namespace Modules\Log\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ModelActionLogged
{
    use Dispatchable, SerializesModels;

    public ?int $userId;
    public string $action;
    public string $model;
    public string $modelUuid;
    public string $ipAddress;
    public ?array $changes;

    public function __construct(?int $userId, string $action, string $model, string $modelUuid, string $ipAddress, ?array $changes = null)
    {
        $this->userId = $userId;
        $this->action = $action;
        $this->model = $model;
        $this->modelUuid = $modelUuid;
        $this->ipAddress = $ipAddress;
        $this->changes = $changes;
    }
}
