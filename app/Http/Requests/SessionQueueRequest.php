<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SessionQueueRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'branch_id'     => 'nullable|integer',
            'room_id'       => 'nullable|integer|exists:rooms,id',
            'service_id'    => 'nullable|integer|exists:items,id',
            'therapist_id'  => 'nullable|integer|exists:users,id',
            'customer_name' => 'required|string|max:255',
            'customer_phone'=> 'nullable|string|max:30',
            'notes'         => 'nullable|string|max:1000',
            'position'      => 'nullable|integer|min:0',
            'status'        => 'nullable|in:waiting,called,seated,cancelled',
        ];
    }
}
