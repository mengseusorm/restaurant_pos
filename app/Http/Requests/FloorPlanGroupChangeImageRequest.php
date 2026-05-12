<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FloorPlanGroupChangeImageRequest extends FormRequest
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
            'image' => ['required', 'image', 'mimes:jpg,jpeg,png,gif', 'max:5120'] // 5MB max
        ];
    }
}
