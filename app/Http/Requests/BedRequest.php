<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BedRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'branch_id' => 'required|integer|exists:branches,id',
            'room_id'   => 'required|integer|exists:rooms,id',
            'name'      => 'required|string|max:255',
            'status'    => 'required|in:available,occupied,cleaning',
        ];
    }
}
