<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class LostAndFoundRequest extends FormRequest
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
                        'item_name' => ['required', 'string', 'max:255'],
            'found_date' => ['required', 'date'],
            'found_by' => ['nullable', 'string', 'max:255'],
            'found_location' => ['required', 'string', 'max:255'],
            'customer_name'     => ['nullable', 'string', 'max:255'],
            'customer_phone'    => ['nullable', 'string', 'max:20'],
            'customer_email'    => ['nullable', 'email', 'max:255'],
            'status'            => ['required', 'integer', Rule::in([1, 2, 3])],
            'claimed_by'        => ['nullable', 'string', 'max:255'],
            'claimed_date'      => ['nullable', 'date'],
            'notes'             => ['nullable', 'string'],
            'branch_id'         => ['required', 'integer', 'exists:branches,id'],
            'storage_location'  => ['nullable', 'string', 'max:255'],
            'disposal_date'     => ['nullable', 'date'],
            'image'             => ['nullable', 'image', 'mimes:jpg,jpeg,png,gif', 'max:2048']
        ];
    }
}
