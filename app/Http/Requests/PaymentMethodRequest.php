<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PaymentMethodRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'user_id'         => 'nullable',
            'name'            => 'string', 
            'value'           => 'nullable',
            'provider'        => ['nullable', 'string', Rule::in(['other', 'payway'])],
            'account_name'    => [
                'nullable',
                'string'
            ],
            'account_number'  => [
                'nullable',
                'string'
            ],
            'expiry_date'     => [
                'nullable',
                'string'
            ],
            'billing_address' => [
                'nullable',
                'string'
            ],
            'is_default'      => 'integer',
            'order_number'    => 'integer',
            'status'          => 'integer',
            'show_online_payment' => 'integer',
            'show_table_order_payment' => 'integer',
            'is_pos_static_qr_code_payment' => 'integer',
            'is_pos_bank_integrate_payment' => 'integer',
            'short_description' => 'nullable|string',
            'supported_currencies' => 'nullable|array',
            'supported_currencies.*' => 'exists:currencies,id',
            'pos_static_qr_code' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ];
    }
}
