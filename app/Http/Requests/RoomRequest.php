<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RoomRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'          => 'required|string|max:255',
            'branch_id'     => 'nullable|integer|exists:branches,id',
            'status'        => 'required|in:available,occupied,cleaning',
            'qr_code_token' => 'nullable|string|max:255',
        ];
    }
}
