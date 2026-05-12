<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PrintLabelRequest extends FormRequest
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
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'name'                        => 'required|string|max:255',
            'show_company_name'           => 'required|integer',
            'show_branch_name'            => 'required|integer',
            'show_phone_number'           => 'required|integer',
            'show_order_number'           => 'required|integer',
            'show_order_number_barcode'   => 'required|integer',
            'show_order_qr_code'          => 'required|integer',
            'show_item'                   => 'required|integer',
            'show_item_qty'               => 'required|integer',
            'show_item_price'             => 'required|integer',
            'show_customer_name'          => 'required|integer',
            'show_customer_phone_number'  => 'required|integer',
            'show_delivery_address'       => 'required|integer',
            'show_payment_status'         => 'required|integer',
            'show_payment_qr_code'        => 'required|integer',
            'show_payment_method'         => 'required|integer',
            'print_qty'                   => 'required|integer',
            'label_title'                 => 'required|integer',
            'label_width'                 => 'required|integer|min:10|max:500',
            'label_height'                => 'required|integer|min:10|max:500',
            'separate_item'               => 'required|integer',
            'separate_qty'                => 'required|integer',
            'label_style_custom'          => 'nullable|string|max:10000', 
        ];
    }
}
