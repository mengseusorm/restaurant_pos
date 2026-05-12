<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SessionAddItemRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'branch_id'        => 'nullable|integer|exists:branches,id',
            'item_id'          => 'required|integer|exists:items,id',
            'therapist_id'     => 'nullable|integer|exists:users,id',
            'quantity'         => 'required|integer|min:1|max:99',
            'duration_minutes' => 'nullable|integer|min:1',
            'unit_price'       => 'nullable|numeric|min:0',
            'started_at'       => 'nullable|date',
            'ended_at'         => 'nullable|date|after_or_equal:started_at',
            'notes'            => 'nullable|string|max:255',
            'item_id'      => 'required|integer|exists:items,id',
            'room_id'      => 'nullable|integer|exists:rooms,id',
            'bed_id'       => 'nullable|integer|exists:beds,id',
            'therapist_id' => 'nullable|integer|exists:users,id',
            'start_time'   => 'nullable|date',
            'end_time'     => 'nullable|date',
            'quantity'     => 'nullable|integer|min:1',
            'duration'     => 'nullable|integer|min:0',
            'price'        => 'nullable|numeric|min:0',
            'discount'     => 'nullable|numeric|min:0',
            'notes'        => 'nullable|string|max:255',
        ];
    }
}
