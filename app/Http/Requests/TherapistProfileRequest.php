<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class TherapistProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $therapistProfile = $this->route('therapistProfile');
        $profileUserId = $therapistProfile?->user_id;
        $requestUserId = $this->input('user_id');
        $userId = $profileUserId ?: $requestUserId;
        $creatingNewUser = $this->isMethod('post') && !$requestUserId && !$profileUserId;

        return [
            'name'            => [$creatingNewUser ? 'required' : 'nullable', 'string', 'max:190'],
            'email'           => [
                $creatingNewUser ? 'required' : 'nullable',
                'email',
                'max:190',
                Rule::unique('users', 'email')->ignore($userId),
            ],
            'phone'           => [
                'nullable',
                'string',
                'max:20',
                Rule::unique('users', 'phone')->ignore($userId),
            ],
            'country_code'    => [$creatingNewUser ? 'required' : 'nullable', 'string', 'max:20'],
            'password'        => [$creatingNewUser ? 'required' : 'nullable', 'string', 'min:6', 'confirmed'],
            'password_confirmation' => [$creatingNewUser ? 'required' : 'nullable', 'string', 'min:6'],
            'user_id'         => 'nullable|integer|exists:users,id',
            'code'            => 'nullable|string|max:50',
            'verify_code'     => 'nullable|string|max:16',
            'commission_rate' => 'required|numeric|min:0|max:100',
            'status'          => 'required|in:available,busy,away',
            'branch_id'       => 'required|integer|exists:branches,id',
        ];
    }
}
