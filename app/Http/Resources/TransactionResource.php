<?php

namespace App\Http\Resources;


use App\Libraries\AppLibrary;
use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request): array
    {
        return [
            'id'                     => $this->id,
            'order_id'               => $this->order_id,
            'order_serial_no'        => $this->order?->order_serial_no,
            'transaction_no'         => $this->transaction_no,
            'amount'                 => AppLibrary::flatAmountFormat($this->amount),
            'currency'               => $this->currency,
            'currency_id'            => $this->currency_id,
            'amount_base_currency'   => AppLibrary::flatAmountFormat($this->amount_base_currency),
            'base_currency'          => $this->base_currency,
            'base_currency_id'       => $this->base_currency_id,
            'transaction_amount'     => AppLibrary::flatAmountFormat($this->transaction_amount),
            'transaction_currency'   => $this->transaction_currency,
            'transaction_currency_id' => $this->transaction_currency_id,
            'change_amount'          => AppLibrary::flatAmountFormat($this->change_amount),
            'change_currency'        => $this->change_currency,
            'change_currency_id'     => $this->change_currency_id,
            'exchange_rate'          => $this->exchange_rate,
            'exchange_rate_base'     => $this->exchange_rate_base,
            'exchange_rate_target'   => $this->exchange_rate_target,
            'payment_method'         => strtoupper($this->payment_method),
            'pos_payment_method'     => $this->pos_payment_method,
            'posPaymentMethod'       => new PaymentMethodMinimalResource($this->posPaymentMethod),
            'type'                   => $this->type,
            'sign'                   => $this->sign,
            'reference_transaction'  => $this->reference_transaction,
            'user_id'                => $this->user_id,
            'user_name'              => $this->user?->name,
            'note'                   => $this->note,
            'date'                   => AppLibrary::datetime($this->created_at)
        ];
    }
}
