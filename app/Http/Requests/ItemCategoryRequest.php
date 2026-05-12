<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ItemCategoryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'name'        => [
                'required',
                'string',
                'max:190',
                Rule::unique("item_categories", "name")->ignore($this->route('itemCategory.id'))
            ],
            'name_kh'     => ['nullable', 'string', 'max:190'],
            'name_cn'     => ['nullable', 'string', 'max:190'],
            'name_en'     => ['nullable', 'string', 'max:190'],
            'item_category_code' => ['nullable', 'string', 'max:190'],
            'description' => ['nullable', 'string', 'max:900'],
            'status'      => ['required', 'numeric', 'max:24'],
            'image'       => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
            'branch_id'   => ['nullable', 'numeric','not_in:0'],
            // 'sort'        => ['nullable', Rule::unique('item_categories', 'sort')->ignore($this->route('itemCategory.id'))]
            'sort'        => ['nullable', 'numeric']
        ];
    }
}
