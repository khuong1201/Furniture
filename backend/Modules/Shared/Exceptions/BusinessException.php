<?php

declare(strict_types=1);

namespace Modules\Shared\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;

class BusinessException extends Exception
{
    protected $code = 400; 

    public function render(): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $this->getMessage(),
            'error_code' => $this->getCode(), 
        ], $this->code ?: 400);
    }
}