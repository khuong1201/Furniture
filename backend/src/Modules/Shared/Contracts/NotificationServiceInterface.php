<?php

declare(strict_types=1);

namespace Modules\Shared\Contracts;

interface NotificationServiceInterface
{
    public function send(
        int|string $userId, 
        string $title, 
        string $content, 
        string $type = 'info', 
        array $data = []
    ): void;
}