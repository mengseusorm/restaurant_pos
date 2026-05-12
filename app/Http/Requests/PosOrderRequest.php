<?php

namespace App\Http\Requests;

use App\Enums\Activity;
use App\Enums\OrderType;
use App\Rules\ValidJsonOrder;
use Illuminate\Validation\Rule;
use Smartisan\Settings\Facades\Settings;
use Illuminate\Foundation\Http\FormRequest;
use App\Enums\PosPaymentMethod;

class PosOrderRequest extends FormRequest
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
            'customer_id'     => ['nullable', 'numeric'],
            'branch_id'       => ['required', 'numeric'],

            'subtotal'        => ['required', 'numeric'],
            'discount'        => ['nullable', 'numeric'],
            'discount_percentage' => ['nullable', 'numeric'],
            'total'            => ['required', 'numeric'],
            'total_tax'       => ['nullable', 'numeric'],

            'currency'        => ['required', 'string'],
            'currency_id'     => ['nullable', 'integer', 'exists:currencies,id'],
            'receive_payment_currency' => ['nullable', 'string', 'max:3'],
            'receive_payment_currency_id' => ['nullable', 'integer', 'exists:currencies,id'],

            'delivery_charge' => request('order_type') === OrderType::DELIVERY ? [
                'required',
                'numeric'
            ] : ['nullable'],

            'order_type'       => ['required', 'numeric'],
            'is_advance_order' => ['required', 'numeric'],
            'address_id'       => request('order_type') === OrderType::DELIVERY ? [
                'required',
                'numeric'
            ] : ['nullable'],
            'delivery_time'    => request('order_type') === OrderType::DELIVERY ? [
                'required',
                'string'
            ] : ['nullable'],
            'source'           => ['required', 'numeric'],
            'items'            => ['required', 'json', new ValidJsonOrder],
            'pos_payment_method' => ['required', 'numeric'],
            'pos_payment_note'  => request('pos_payment_method') ===  PosPaymentMethod::CASH ?  ['nullable'] : (PosPaymentMethod::CARD ?  ['nullable'] : (PosPaymentMethod::ABA ?  ['nullable'] : (PosPaymentMethod::ACLEDA ?  ['nullable'] : ['required']))),
            'pos_received_amount' => ['nullable', 'numeric'],
            'payment_method' => ['nullable', 'numeric'],
            'order_note' => ['nullable', 'string'],
            'number_of_people' => ['nullable', 'numeric'],
            'member_id' => ['nullable', 'integer', 'exists:members,id'],
            'points_to_redeem' => ['nullable', 'integer', 'min:0'],
            'order_user_id' => ['nullable', 'integer', 'exists:users,id'],
            'customer_name' => ['nullable', 'string', 'max:255'],
            'customer_phone_number' => ['nullable', 'string', 'max:20'],
            'customer_address' => ['nullable', 'string', 'max:500'],
            'check_in_time' => ['nullable', 'date'],
            'check_out_time' => ['nullable', 'date'],
            'checkout' => ['nullable', 'date'],
            'preparation_time' => ['nullable', 'integer', 'min:0'],
            'payment_transaction_id' => ['nullable', 'string', 'max:255'],
            'payment_transaction_data' => ['nullable', 'json'],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if (request('order_type') == OrderType::DELIVERY && Settings::group('order_setup')->get("order_setup_delivery") == Activity::DISABLE) {
                $validator->errors()->add('order_type', 'This order type is disabled now you can try another order type right now or call the management.');
            } else if (request('order_type') == OrderType::TAKEAWAY && Settings::group('order_setup')->get("order_setup_takeaway") == Activity::DISABLE) {
                $validator->errors()->add('order_type', 'This order type is disabled now you can try another order type right now or call the management.');
            } else if (blank(request('order_type'))) {
                $validator->errors()->add('order_type', 'This order type is disabled now you can try another order type right now or call the management.');
            }
        });
    }

    public function messages(){
        return [
            'pos_payment_note.required' => 'Payment note field is required '
        ];
    }
}
