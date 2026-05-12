<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BatchApplyVariationRequest extends FormRequest
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
            'variations' => ['required', 'array'],
            'variations.*.item_id' => ['required', 'numeric', 'exists:items,id'],
            'variations.*.item_attribute_variation_id' => ['required', 'numeric', 'exists:item_attribute_variations,id'],
            'variations.*.price' => ['nullable', 'numeric'],
        ];
    }
}
