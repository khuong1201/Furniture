<?php

declare(strict_types=1);

namespace Modules\Order\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Modules\Shared\Http\Resources\ApiResponse;
use OpenApi\Attributes as OA;

class CheckoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'address_id' => 'required|integer|exists:addresses,id',
            'notes' => 'nullable|string|max:500',
            
            'selected_item_uuids' => 'nullable|array',
            'selected_item_uuids.*' => 'required_with:selected_item_uuids|uuid',
        ];
    }
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            response()->json(
                ApiResponse::error('Dữ liệu thanh toán không hợp lệ.', 422, $validator->errors()), 
                422
            )
        );
    }
}