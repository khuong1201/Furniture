<?php

declare(strict_types=1);

namespace Modules\Promotion\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePromotionRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        // Lấy ID của promotion hiện tại để validate unique (nếu cần thiết cho trường nào đó)
        // $uuid = $this->route('uuid'); 

        return [
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            
            'type' => 'sometimes|in:percentage,fixed',
            
            // CHANGE: Sử dụng integer cho BigInteger (tiền tệ)
            'value' => 'sometimes|integer|min:0',
            
            'start_date' => 'sometimes|date',
            // Rule 'after:start_date' chỉ hoạt động tốt nếu start_date cũng được gửi lên, 
            // hoặc bạn phải custom validation nếu start_date không được gửi.
            // Ở đây giữ đơn giản theo convention API: thường gửi cả cặp date nếu sửa thời gian.
            'end_date' => 'sometimes|date|after:start_date',
            
            'is_active' => 'sometimes|boolean',
            
            // Sync products
            'product_ids' => 'sometimes|array',
            'product_ids.*' => 'integer|exists:products,id',
            
            // CHANGE: Sử dụng integer
            'min_order_value' => 'nullable|integer|min:0',
            'max_discount_amount' => 'nullable|integer|min:0',
            
            'quantity' => 'sometimes|integer|min:0',
            'limit_per_user' => 'sometimes|integer|min:1',
        ];
    }
}