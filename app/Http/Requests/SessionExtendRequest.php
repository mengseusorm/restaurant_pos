<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SessionExtendRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'extra_minutes' => 'required|integer|min:1|max:480',
        ];
    }
}
