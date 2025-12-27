<?php

namespace Modules\Product\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Product\Domain\Models\Product;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        // 1. Lấy đúng tham số UUID từ URL
        $uuid = $this->route('uuid'); 
        
        // 2. Tìm sản phẩm (kể cả đã xóa mềm)
        $product = Product::withTrashed()->where('uuid', $uuid)->firstOrFail(); 
        
        $rules = [
            'name' => 'sometimes|required|string|max:150',
            'slug' => ['nullable', 'string', 'max:150', Rule::unique('products', 'slug')->ignore($product->id)],
            'category_uuid' => 'sometimes|required|exists:categories,uuid',
            'brand_uuid' => 'nullable|exists:brands,uuid',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'images' => 'nullable|array',
            'warehouse_stock' => 'nullable|array',
            'deleted_image_uuids' => 'nullable|array',
            'current_images_state' => 'nullable|array',
        ];

        // --- FIX LOGIC SKU ---
        if (!$product->has_variants) {
            $rules['price'] = 'sometimes|required|numeric|min:0';
            
            $rules['sku'] = [
                'sometimes', 'required', 'string', 'max:100',
                // 1. Check trùng trong bảng products (Trừ chính nó ra)
                Rule::unique('products', 'sku')->ignore($product->id),
                
                // 2. Check trùng trong bảng product_variants
                // FIX: Chỉ báo lỗi nếu SKU trùng với variant của SẢN PHẨM KHÁC
                // (Cho phép trùng với variant con của chính nó)
                Rule::unique('product_variants', 'sku')->where(function ($query) use ($product) {
                    return $query->where('product_id', '!=', $product->id);
                })
            ];
        } 
        else {
            $rules['variants'] = 'sometimes|required|array';
            // Khi update variants, ta cho phép sku trùng với sku cũ, nhưng không trùng với sp khác
            // Logic này phức tạp hơn nếu muốn ignore từng dòng, nhưng 'distinct' giúp check nội bộ mảng gửi lên
            $rules['variants.*.sku'] = 'required|string|distinct';
            $rules['variants.*.price'] = 'required|numeric|min:0';
        }

        return $rules;
    }
}