<?php

declare(strict_types=1);

namespace Modules\Notification\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class NotificationRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'title'   => 'required|string|max:255',
            'content' => 'required|string',
            'type'    => 'nullable|in:info,warning,alert,success',
        ];
    }
}