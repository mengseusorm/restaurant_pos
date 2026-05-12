<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MemberRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:190'],
            'phone' => [
                'required',
                'string',
                'max:20',
                Rule::unique("members", "phone")->ignore($this->route('member.id'))
            ],
            'user_id' => ['nullable', 'exists:users,id'],
            'branch_id' => ['nullable', 'exists:branches,id'],
            'card_number' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique("members", "card_number")->ignore($this->route('member.id'))
            ],
            'point_balance' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Member name is required',
            'phone.required' => 'Phone number is required',
            'phone.unique' => 'This phone number is already registered',
            'user_id.exists' => 'Selected user does not exist',
            'card_number.unique' => 'This card number is already in use',
            'point_balance.min' => 'Point balance cannot be negative',
        ];
    }
}
