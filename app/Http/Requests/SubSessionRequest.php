<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SubSessionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'group_session_id' => 'nullable|integer|exists:group_sessions,id',
            'guest_name'       => 'required|string|max:255',
            'phone'            => 'nullable|string|max:30',
            'status'           => 'sometimes|in:waiting,in_service,done',
            'start_time'       => 'nullable|date',
            'end_time'         => 'nullable|date|after_or_equal:start_time',
            'is_checked_out'   => 'sometimes|boolean',
            'share_group_bill' => 'sometimes|boolean',
            'notes'            => 'nullable|string|max:1000',
        ];
    }
}
