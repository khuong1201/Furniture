<?php

declare(strict_types=1);

namespace Modules\Review\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateReviewRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        $rules = [
            'rating'  => 'sometimes|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
        ];

        if ($this->user() && $this->user()->hasRole('admin')) {
            $rules['is_approved'] = 'sometimes|boolean';
        }

        return $rules;
    }
}