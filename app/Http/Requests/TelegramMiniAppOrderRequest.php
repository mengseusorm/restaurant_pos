<?php

namespace App\Http\Requests;

use App\Enums\Activity;
use App\Enums\OrderType;
use App\Rules\ValidJsonOrder;
use Illuminate\Validation\Rule;
use Smartisan\Settings\Facades\Settings;
use Illuminate\Foundation\Http\FormRequest;

class TelegramMiniAppOrderRequest extends FormRequest
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
            'customer_id'      => ['nullable', 'numeric'],
            'branch_id'        => ['required', 'numeric'],
            'subtotal'         => ['required', 'numeric'],
            'discount'         => ['nullable', 'numeric'],
            'total'            => ['required', 'numeric'],
            'order_type'       => ['required', 'numeric'],
            'is_advance_order' => ['required', 'numeric'],
            'source'           => ['required', 'numeric'],
            'items'            => ['required', 'json', new ValidJsonOrder],
            'payment_method_id' => ['nullable', 'numeric'],
            'payment_method' => ['nullable', 'numeric'],
            'order_note' => ['nullable', 'string'],
            'customer_name' => ['nullable', 'string', 'max:255'],
            'customer_phone_number' => ['nullable', 'string', 'max:20'],
            'customer_address' => ['nullable', 'string', 'max:500'],
            // PayWay transaction fields
            'payment_transaction_id' => ['nullable', 'string', 'max:255'],
            'payment_transaction_data' => ['nullable', 'json'],
            'currency' => ['nullable', 'string', 'max:10'],
            'currency_id' => ['nullable', 'integer', 'exists:currencies,id'],
            'receive_payment_currency' => ['nullable', 'string', 'max:3'],
            'receive_payment_currency_id' => ['nullable', 'integer', 'exists:currencies,id'],
            // Telegram-specific fields
            'telegram_user_id' => ['nullable', 'string', 'max:255'],
            'telegram_chat_id' => ['nullable', 'string', 'max:255'],
            'telegram_username' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'customer_id.numeric'      => trans('validation.numeric', ['attribute' => trans('validation.attributes.customer_id')]),
            'branch_id.required'       => trans('validation.required', ['attribute' => trans('validation.attributes.branch_id')]),
            'branch_id.numeric'        => trans('validation.numeric', ['attribute' => trans('validation.attributes.branch_id')]),
            'subtotal.required'        => trans('validation.required', ['attribute' => trans('validation.attributes.subtotal')]),
            'subtotal.numeric'         => trans('validation.numeric', ['attribute' => trans('validation.attributes.subtotal')]),
            'discount.numeric'         => trans('validation.numeric', ['attribute' => trans('validation.attributes.discount')]),
            'total.required'           => trans('validation.required', ['attribute' => trans('validation.attributes.total')]),
            'total.numeric'            => trans('validation.numeric', ['attribute' => trans('validation.attributes.total')]),
            'order_type.required'      => trans('validation.required', ['attribute' => trans('validation.attributes.order_type')]),
            'order_type.numeric'       => trans('validation.numeric', ['attribute' => trans('validation.attributes.order_type')]),
            'is_advance_order.required' => trans('validation.required', ['attribute' => trans('validation.attributes.is_advance_order')]),
            'is_advance_order.numeric' => trans('validation.numeric', ['attribute' => trans('validation.attributes.is_advance_order')]),
            'source.required'          => trans('validation.required', ['attribute' => trans('validation.attributes.source')]),
            'source.numeric'           => trans('validation.numeric', ['attribute' => trans('validation.attributes.source')]),
            'items.required'           => trans('validation.required', ['attribute' => trans('validation.attributes.items')]),
            'items.json'               => trans('validation.json', ['attribute' => trans('validation.attributes.items')]),
        ];
    }
}