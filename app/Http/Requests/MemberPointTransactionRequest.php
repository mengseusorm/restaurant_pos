<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MemberPointTransactionRequest extends FormRequest
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
            'member_id' => ['required', 'exists:members,id'],
            'branch_id' => ['nullable', 'exists:branches,id'],
            'type' => ['required', 'string', Rule::in(['earn', 'redeem', 'revert_earn', 'revert_redeem'])],
            'points' => ['required', 'integer', 'min:1'],
            'reference_type' => ['nullable', 'string', 'max:100'],
            'reference_id' => ['nullable', 'integer'],
            'note' => ['nullable', 'string', 'max:255'],
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
            'member_id.required' => 'Member is required',
            'member_id.exists' => 'Selected member does not exist',
            'type.required' => 'Transaction type is required',
            'type.in' => 'Transaction type must be one of: earn, redeem, revert_earn, revert_redeem',
            'points.required' => 'Points amount is required',
            'points.min' => 'Points amount must be at least 1',
            'points.integer' => 'Points amount must be a valid number',
        ];
    }
}
