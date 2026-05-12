<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ItemAttributeVariationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize() : bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules() : array
    {
        return [
            'item_attribute_id' => ['required', 'numeric', 'exists:item_attributes,id'],
            'name'              => ['required', 'string', 'max:190'],
            'price'             => ['required', 'numeric'],
            'caution'           => ['nullable', 'string', 'max:255'],
            'status'            => ['required', 'numeric', 'max:24']
        ];
    }
}
