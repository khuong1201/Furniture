<?php

declare(strict_types=1);

namespace Modules\Wishlist\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ToggleWishlistRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; 
    }

    public function rules(): array
    {
        return [
            'product_uuid' => 'required|string|uuid|exists:products,uuid',
        ];
    }
}