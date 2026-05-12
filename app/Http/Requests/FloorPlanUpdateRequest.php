<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FloorPlanUpdateRequest extends FormRequest
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
            'position_x' => 'nullable|numeric|min:0|max:2000',
            'position_y' => 'nullable|numeric|min:0|max:2000',
            'width' => 'nullable|numeric|min:40|max:200',
            'height' => 'nullable|numeric|min:40|max:200',
            'rotation' => 'nullable|integer|min:0|max:360',
            'shape' => 'nullable|in:rectangle,circle,square',
            'color' => 'nullable|regex:/^#[0-9A-F]{6}$/i',
            'current_guests' => 'nullable|integer|min:0|max:50',
            'floor_plan_group_id' => 'nullable|exists:floor_plan_groups,id'
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
            'position_x.numeric' => 'Position X must be a number',
            'position_x.min' => 'Position X must be at least 0',
            'position_x.max' => 'Position X cannot exceed 2000',
            'position_y.numeric' => 'Position Y must be a number',
            'position_y.min' => 'Position Y must be at least 0',
            'position_y.max' => 'Position Y cannot exceed 2000',
            'width.numeric' => 'Width must be a number',
            'width.min' => 'Width must be at least 40',
            'width.max' => 'Width cannot exceed 200',
            'height.numeric' => 'Height must be a number',
            'height.min' => 'Height must be at least 40',
            'height.max' => 'Height cannot exceed 200',
            'rotation.integer' => 'Rotation must be an integer',
            'rotation.min' => 'Rotation must be at least 0',
            'rotation.max' => 'Rotation cannot exceed 360',
            'shape.in' => 'Shape must be rectangle, circle, or square',
            'color.regex' => 'Color must be a valid hex code (e.g., #FF0000)',
            'current_guests.integer' => 'Current guests must be an integer',
            'current_guests.min' => 'Current guests must be at least 0',
            'current_guests.max' => 'Current guests cannot exceed 50',
            'floor_plan_group_id.exists' => 'Selected floor plan group does not exist',
        ];
    }
}
