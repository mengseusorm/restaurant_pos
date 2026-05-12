<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CombineOrderRequest extends FormRequest
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
            'source_order_ids' => ['required', 'array', 'min:1'],
            'source_order_ids.*' => ['required', 'integer', 'exists:orders,id'],
            'target_order_id' => ['required', 'integer', 'exists:orders,id'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'source_order_ids.required' => 'Please select at least one order to combine.',
            'source_order_ids.array' => 'Invalid source orders format.',
            'source_order_ids.min' => 'Please select at least one order to combine.',
            'source_order_ids.*.required' => 'Source order ID is required.',
            'source_order_ids.*.integer' => 'Source order ID must be a valid number.',
            'source_order_ids.*.exists' => 'One or more selected source orders do not exist.',
            'target_order_id.required' => 'Please select a target order.',
            'target_order_id.integer' => 'Target order ID must be a valid number.',
            'target_order_id.exists' => 'The selected target order does not exist.',
        ];
    }

    /**
     * Configure the validator instance.
     *
     * @param  \Illuminate\Validation\Validator  $validator
     * @return void
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Ensure target order is not in source orders
            if (in_array($this->target_order_id, $this->source_order_ids ?? [])) {
                $validator->errors()->add('target_order_id', 'Target order cannot be one of the source orders.');
            }
        });
    }
}
