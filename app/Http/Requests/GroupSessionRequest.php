<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GroupSessionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'code'              => 'nullable|string|max:30',
            'notes'             => 'nullable|string|max:1000',
            'status'            => 'sometimes|in:open,in_progress,completed,cancelled',
            'total_guests'      => 'sometimes|integer|min:0',
            'is_group_checkout' => 'sometimes|boolean',
            'arrival_time'      => 'nullable|date',
            'end_time'          => 'nullable|date',
        ];
    }
}
