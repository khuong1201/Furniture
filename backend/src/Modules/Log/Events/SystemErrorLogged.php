<?php
namespace Modules\Log\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SystemErrorLogged
{
    use Dispatchable, SerializesModels;

    public ?int $userId;
    public string $message;
    public string $ipAddress;
    public array $metadata;

    public function __construct(?int $userId, string $message, string $ipAddress, array $metadata = [])
    {
        $this->userId = $userId;
        $this->message = $message;
        $this->ipAddress = $ipAddress;
        $this->metadata = $metadata;
    }
}
