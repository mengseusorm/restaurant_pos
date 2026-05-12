<?php

namespace App\Http\Requests;

use App\Enums\Status;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ExpenseTypeRequest extends FormRequest
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
            'branch_id' => ['required', 'numeric', 'not_in:0'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'is_active' => ['sometimes', 'numeric', Rule::in([Status::ACTIVE, Status::INACTIVE])],
        ];

        return $rules;
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'name' => 'expense type name',
            'description' => 'description',
            'is_active' => 'active status',
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'The expense type name is required.',
            'name.string' => 'The expense type name must be a valid text.',
            'name.max' => 'The expense type name cannot exceed 255 characters.',
            'description.string' => 'The description must be a valid text.',
        ];
    }
}
