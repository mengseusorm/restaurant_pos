<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class OrderTypeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'branch_id'  => 'nullable|integer',
            'type_code'  => [
                'required',
                'integer',
                Rule::unique('order_types', 'type_code')->ignore($this->route('orderType.id')),
            ],
            'name'       => 'required|string|max:255',
            'name_kh'    => 'nullable|string|max:255',
            'name_cn'    => 'nullable|string|max:255',
            'name_en'    => 'nullable|string|max:255',
            'type_order' => 'required|integer|min:0',
            'status'     => 'required|integer|in:5,10',
        ];
    }
}
