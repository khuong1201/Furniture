<?php
namespace Modules\User\Traits;
trait ApiResponse
{
    protected function success($data = [], $message = 'Thành công', $code = 200)
    {
        return response()->json([
            'status' => true,
            'message' => $message,
            'data' => $data,
        ], $code);
    }

    protected function error($message = 'Lỗi xảy ra', $code = 400)
    {
        return response()->json([
            'status' => false,
            'message' => $message,
        ], $code);
    }
}
