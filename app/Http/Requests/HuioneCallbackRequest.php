<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class HuioneCallbackRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // Allow callback requests without authentication
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'appId' => 'required|string',
            'hash' => 'required|string',
            'merchantId' => 'required|string',
            'outTradeNo' => 'required|string',
            'status' => 'required|string|in:DONE_PAYMENT,FAIl_PAYMENT,CANCEL_PAYMENT,CANCELLED,FAILED_PAYMENT,FAILED,PENDING_PAYMENT,PENDING',
            'transactionId' => 'required|string',
            'attach' => 'nullable|string',
            'nonce' => 'required|string',
            'sign' => 'required|string',
            'timestamp' => 'required|integer'
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'appId.required' => 'App ID is required',
            'hash.required' => 'Hash is required',
            'merchantId.required' => 'Merchant ID is required',
            'outTradeNo.required' => 'Order number is required',
            'status.required' => 'Payment status is required',
            'status.in' => 'Invalid payment status',
            'transactionId.required' => 'Transaction ID is required',
            'nonce.required' => 'Nonce is required',
            'sign.required' => 'Signature is required',
            'timestamp.required' => 'Timestamp is required',
            'timestamp.integer' => 'Timestamp must be an integer'
        ];
    }
}
