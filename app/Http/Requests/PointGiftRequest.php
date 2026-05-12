<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PointGiftRequest extends FormRequest
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
            'item_id' => 'required|integer|exists:items,id',
            'required_points' => 'required|integer|min:1|max:999999',
            'stock_limit' => 'nullable|integer|min:0|max:999999',
            'redeemed_count' => 'sometimes|integer|min:0|max:999999',
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
            'item_id' => 'item',
            'required_points' => 'required points',
            'stock_limit' => 'stock limit',
            'redeemed_count' => 'redeemed count',
            'is_active' => 'active status', 
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     */
    public function messages(): array
    {
        return [
            'item_id.required' => 'The item is required.',
            'item_id.exists' => 'The selected item does not exist.',
            'required_points.required' => 'The required points is required.',
            'required_points.integer' => 'The required points must be a whole number.',
            'required_points.min' => 'The required points must be at least 1.',
            'required_points.max' => 'The required points cannot exceed 999,999.',
            'stock_limit.integer' => 'The stock limit must be a whole number.',
            'stock_limit.min' => 'The stock limit cannot be negative.',
            'stock_limit.max' => 'The stock limit cannot exceed 999,999.',
            'redeemed_count.integer' => 'The redeemed count must be a whole number.',
            'redeemed_count.min' => 'The redeemed count cannot be negative.',
        ];
    }
}
