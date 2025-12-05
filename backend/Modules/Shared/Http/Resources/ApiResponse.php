<?php

declare(strict_types=1);

namespace Modules\Shared\Http\Resources;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ApiResponse
{
    public static function success(mixed $data = null, string $message = 'Success', int $code = 200): array
    {
        return [
            'success' => true,
            'message' => $message,
            'data' => $data,
        ];
    }

    public static function error(string $message, int $code = 400, mixed $errors = null): array
    {
        return [
            'success' => false,
            'message' => $message,
            'errors' => $errors,
        ];
    }

    public static function paginated(LengthAwarePaginator $data, string $message = 'Success'): array
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
                'from' => $data->firstItem(),
                'to' => $data->lastItem(),
            ],
        ];
    }
}