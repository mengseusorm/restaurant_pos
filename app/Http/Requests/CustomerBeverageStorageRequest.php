<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CustomerBeverageStorageRequest extends FormRequest
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
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_phone' => ['required', 'string', 'max:20'],
            'beverage_name' => ['required', 'string', 'max:255'],
            'quantity' => ['nullable', 'numeric', 'min:0'],
            'original_quantity' => ['nullable', 'numeric', 'min:0'],
            'unit' => ['required', 'string', 'max:50'],
            'store_date' => ['required', 'date'],
            'expiry_date' => ['nullable', 'date', 'after_or_equal:store_date'],
            'status' => ['required', 'integer', 'in:1,2,3,4'],
            'storage_location' => ['nullable', 'string', 'max:255'],
            'claimed_date' => ['nullable', 'date'],
            'disposed_date' => ['nullable', 'date'],
            'disposed_reason' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
            'branch_id' => ['required', 'integer', 'exists:branches,id'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,gif', 'max:2048'],
        ];
    }
}
