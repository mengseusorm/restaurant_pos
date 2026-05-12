<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TransferOrderItemsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'sourceOrderId' => 'required|integer|exists:orders,id',
            'targetOrderId' => 'required|integer|exists:orders,id|different:sourceOrderId',
            'items' => 'required|array|min:1',
            'items.*.orderItemId' => 'required|integer|exists:order_items,id',
            'items.*.quantity' => 'required|integer|min:1',
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
            'sourceOrderId.required' => 'Source order is required.',
            'sourceOrderId.exists' => 'Source order does not exist.',
            'targetOrderId.required' => 'Target order is required.',
            'targetOrderId.exists' => 'Target order does not exist.',
            'targetOrderId.different' => 'Target order must be different from source order.',
            'items.required' => 'At least one item is required to transfer.',
            'items.*.orderItemId.required' => 'Order item ID is required.',
            'items.*.orderItemId.exists' => 'Order item does not exist.',
            'items.*.quantity.required' => 'Quantity is required for each item.',
            'items.*.quantity.min' => 'Quantity must be at least 1.',
        ];
    }
}
