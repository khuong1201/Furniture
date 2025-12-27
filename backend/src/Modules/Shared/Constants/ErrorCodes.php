<?php

declare(strict_types=1);

namespace Modules\Shared\Constants;

class ErrorCodes
{
    // Auth Module (02)
    public const AUTH_INVALID_TOKEN = 401020;
    public const AUTH_TOKEN_EXPIRED = 401021;
    public const AUTH_FORBIDDEN     = 403023;

    // Order Module (13)
    public const ORDER_NOT_FOUND    = 404130;
    public const ORDER_INVALID_STATUS = 409132;
    
    // Inventory (09)
    public const INVENTORY_OUT_OF_STOCK = 409091;

    public static function getMessage(int $code): string
    {
        $messages = config('error_codes.messages', []); 
        return $messages[$code]['message'] ?? 'Unknown Error';
    }
}