<?php

declare(strict_types=1);

namespace Modules\Shared\Http\Traits;

use Illuminate\Http\JsonResponse;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

trait ApiResponseTrait
{
    public function successResponse(mixed $data, string $message = 'Success', int $status = 200): JsonResponse
    {
        $response = [
            'success' => true,
            'message' => $message,
            'data'    => $data,
        ];

        if ($data instanceof LengthAwarePaginator) {
            $response['data'] = $data->items();
            $response['meta'] = [
                'current_page' => $data->currentPage(),
                'total'        => $data->total(),
                'per_page'     => $data->perPage(),
                'last_page'    => $data->lastPage(),
            ];
        }

        return response()->json($response, $status);
    }
    
    public function errorResponse(string $message, int $errorCode = 500000, int $status = 500): JsonResponse
    {
        return response()->json([
            'success'    => false,
            'message'    => $message,
            'error_code' => $errorCode,
        ], $status);
    }
}