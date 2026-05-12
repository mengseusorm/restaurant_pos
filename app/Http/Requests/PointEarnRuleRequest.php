<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PointEarnRuleRequest extends FormRequest
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
     */
    public function rules(): array
    {
        $rules = [
            'branch_id'             => ['required', 'numeric', 'not_in:0'],
            'currency_amount' => 'required|numeric|min:0.01|max:999999.99',
            'point' => 'required|integer|min:1|max:999999',
            'is_active' => 'sometimes|boolean',
            'name'        => ['required', 'string'],
        ];

        return $rules;
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'currency_amount' => 'currency amount',
            'point' => 'points',
            'is_active' => 'active status', 
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     */
    public function messages(): array
    {
        return [
            'currency_amount.required' => 'The currency amount is required.',
            'currency_amount.numeric' => 'The currency amount must be a valid number.',
            'currency_amount.min' => 'The currency amount must be at least 0.01.',
            'currency_amount.max' => 'The currency amount cannot exceed 999,999.99.',
            'point.required' => 'The points value is required.',
            'point.integer' => 'The points must be a whole number.',
            'point.min' => 'The points must be at least 1.',
            'point.max' => 'The points cannot exceed 999,999.',
        ];
    }
}
