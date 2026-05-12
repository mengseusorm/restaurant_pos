<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ExpenseRequest extends FormRequest
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
            'branch_id'         => ['required', 'integer', 'exists:branches,id'],
            'expense_date'      => ['required', 'date'],
            'expense_type_id'   => ['required', 'integer', 'exists:expense_types,id'],
            'amount'            => ['required', 'numeric', 'min:0'],
            'payment_method_id' => ['required', 'integer', 'exists:expense_payment_methods,id'],
            'description'       => ['nullable', 'string', 'max:1000'],
            'paid_to'           => ['nullable', 'string', 'max:255'],
            'reference_no'      => ['nullable', 'string', 'max:255'],
            'status'            => ['nullable', Rule::in(['pending', 'approved', 'rejected'])],
            'receipt_image'     => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:5120']
        ];
    }
}
