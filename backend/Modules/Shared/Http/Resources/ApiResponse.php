<?php

namespace Modules\Shared\Http\Resources;

class ApiResponse
{
    public static function success($data = null, string $message = '', int $code = 200): array
    {
        return [
            'success' => true,
            'message' => $message,
            'data' => $data,
        ];
    }

    public static function error(string $message, int $code = 400, $errors = null): array
    {
        return [
            'success' => false,
            'message' => $message,
            'errors' => $errors,
        ];
    }

    public static function paginated($data, string $message = ''): array
    {
        return [
            'success' => true,
            'message' => $message,
            'data' => $data->items(),
            'meta' => [
                'current_page' => $data->currentPage(),
                'last_page' => $data->lastPage(),
                'per_page' => $data->perPage(),
                'total' => $data->total(),
            ],
        ];
    }
}