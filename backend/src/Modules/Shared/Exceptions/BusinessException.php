<?php

declare(strict_types=1);

namespace Modules\Shared\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;

class BusinessException extends Exception
{
    protected int $errorCode;
    protected int $httpStatus;
    protected array $errors;
    protected string $description;

    public function __construct(
        int $errorCode, 
        ?string $customMessage = null, 
        ?int $httpStatus = null,
        array $errors = []
    ) {
        $errorConfig = $this->getErrorConfig($errorCode);

        $message = $customMessage ?? $errorConfig['message'];
        $this->httpStatus = $httpStatus ?? $errorConfig['http'];
        $this->description = $errorConfig['description'] ?? '';
        
        parent::__construct($message);

        $this->errorCode = $errorCode;
        $this->errors = $errors;
    }

    protected function getErrorConfig(int $code): array
    {
        $codeStr = (string) $code;

        $moduleKey = substr($codeStr, 3, 2); 

        $config = config("error_codes.{$moduleKey}.{$code}");

        if (!$config) {
            return [
                'http' => 400,
                'message' => "Unknown Error ({$code})",
                'description' => 'Error code not defined in system config.'
            ];
        }

        return $config;
    }

    public function render($request): JsonResponse
    {
        return response()->json([
            'success'    => false,
            'message'    => $this->getMessage(),
            'error_code' => $this->errorCode,
            'description' => config('app.debug') ? $this->description : null,
            'errors'     => !empty($this->errors) ? $this->errors : null,
            'timestamp'  => now()->toISOString(),
        ], $this->httpStatus);
    }
}