<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PointUsageRuleRequest extends FormRequest
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
            'name' => 'required|string|max:255',
            'usage_type' => 'required|string|in:deduct_order,exchange_gift',
            'point_to_currency' => 'required|numeric|min:0.01|max:999999.99',
            'min_point_usage' => 'required|integer|min:1|max:999999',
            'max_point_usage' => 'nullable|integer|min:1|max:999999|gte:min_point_usage',
            'is_active' => 'sometimes|boolean',
        ];

        return $rules;
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'name' => 'rule name',
            'usage_type' => 'usage type',
            'point_to_currency' => 'point to currency ratio',
            'min_point_usage' => 'minimum point usage',
            'max_point_usage' => 'maximum point usage',
            'is_active' => 'active status', 
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'The rule name is required.',
            'name.max' => 'The rule name cannot exceed 255 characters.',
            'usage_type.required' => 'The usage type is required.',
            'usage_type.in' => 'The usage type must be either deduct_order or exchange_gift.',
            'point_to_currency.required' => 'The point to currency ratio is required.',
            'point_to_currency.numeric' => 'The point to currency ratio must be a valid number.',
            'point_to_currency.min' => 'The point to currency ratio must be at least 0.01.',
            'min_point_usage.required' => 'The minimum point usage is required.',
            'min_point_usage.integer' => 'The minimum point usage must be a whole number.',
            'min_point_usage.min' => 'The minimum point usage must be at least 1.',
            'max_point_usage.integer' => 'The maximum point usage must be a whole number.',
            'max_point_usage.gte' => 'The maximum point usage must be greater than or equal to minimum point usage.',
        ];
    }
}
