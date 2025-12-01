<?php

namespace Modules\Shared\Exceptions;

use Exception;

class BusinessException extends Exception
{
    protected $statusCode = 400;

    public function render()
    {
        return response()->json([
            'success' => false,
            'message' => $this->getMessage(),
        ], $this->statusCode);
    }
}