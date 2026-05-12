<?php

namespace App\Http\Requests;

use App\Rules\IniAmount;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ItemRequest extends FormRequest
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
            'name'            => [
                'required',
                'string',
                'max:190',
                Rule::unique("items", "name")->whereNull('deleted_at')->ignore($this->route('item.id'))
            ],
            'name_kh'               => ['nullable', 'string', 'max:190'],
            'name_cn'               => ['nullable', 'string', 'max:190'],
            'name_en'               => ['nullable', 'string', 'max:190'],
            'item_code'             => ['nullable', 'string', 'max:190'],
            'item_category_id'      => ['required', 'numeric', 'not_in:0'],
            'tax_id'                => ['nullable', 'numeric', 'not_in:0'],
            'item_type'             => ['required', 'numeric', 'not_in:0'],
            'price'                 => ['required', new IniAmount()],

            'tax_name'              => ['nullable', 'string', 'max:190'],
            'tax_rate'              => ['nullable'],
            'tax_type'              => ['nullable'],
            'tax_amount'            => ['nullable'],
            'price_with_tax'        => ['nullable'],

            'is_featured'           => ['required', 'numeric', 'not_in:0'],
            'description'           => ['nullable', 'string', 'max:5000'],
            'caution'               => ['nullable', 'string', 'max:5000'],
            'status'                => ['required', 'numeric', 'max:24'],
            'order'                 => ['required', 'numeric'],
            'variations'            => ['nullable', 'json'],
            'image'                 => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'branch_id'             => ['required', 'numeric', 'not_in:0'],
            'kitchen_printer_id'    => ['nullable'],
            'label_printer_id'      => ['nullable'],
            'barcode'               => ['nullable'],
            'manage_stock'          => ['nullable', 'numeric', 'not_in:0'],
            'is_print_menu'         => ['nullable', 'numeric'],
            'is_print_label'        => ['nullable', 'numeric'],
            'can_input_custom_name' => ['nullable', 'numeric'],
            'can_input_custom_unit_price' => ['nullable', 'numeric'],
            'item_kind' => ['nullable', 'numeric'],
            'duration'  => ['nullable', 'integer', 'min:1'],
        ];
    }

    public function attributes()
    {
        return [
            'item_category_id' => strtolower(trans('all.label.item_category_id')),
            'tax_id'           => strtolower(trans('all.label.tax_id')),
        ];
    }
}
