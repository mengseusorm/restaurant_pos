<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GroupSessionCheckoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'payment_method'    => 'nullable|string|max:100',
            'payment_method_id' => 'nullable|integer|exists:payment_methods,id',
            'note'              => 'nullable|string|max:500',
        ];
    }
}
