<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SessionAddItemsRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'items'                  => 'required|array|min:1',
            'items.*.item_id'        => 'required|integer|exists:items,id',
            'items.*.room_id'        => 'nullable|integer|exists:rooms,id',
            'items.*.bed_id'         => 'nullable|integer|exists:beds,id',
            'items.*.therapist_id'   => 'nullable|integer|exists:users,id',
            'items.*.start_time'     => 'nullable|date',
            'items.*.end_time'       => 'nullable|date',
            'items.*.quantity'       => 'nullable|integer|min:1',
            'items.*.duration'       => 'nullable|integer|min:0',
            'items.*.price'          => 'nullable|numeric|min:0',
            'items.*.discount'       => 'nullable|numeric|min:0',
            'items.*.notes'          => 'nullable|string|max:255',
        ];
    }
}
