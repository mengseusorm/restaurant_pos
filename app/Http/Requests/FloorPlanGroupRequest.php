<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FloorPlanGroupRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'branch_id' => ['required', 'numeric', 'not_in:0'],
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'sort_order' => 'nullable|integer|min:0',
            'status' => 'nullable|integer|in:5,10'
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
            'name.required' => 'Floor plan group name is required',
            'name.string' => 'Floor plan group name must be a string',
            'name.max' => 'Floor plan group name cannot exceed 255 characters',
            'description.string' => 'Description must be a string',
            'description.max' => 'Description cannot exceed 1000 characters',
            'sort_order.integer' => 'Sort order must be an integer',
            'sort_order.min' => 'Sort order must be at least 0',
            'status.integer' => 'Status must be an integer',
            'status.in' => 'Status must be either active (5) or inactive (10)',
        ];
    }
}
