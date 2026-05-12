<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PaymentMethodResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request) : array
    {
        return [
            "id"              => $this->id,
            'user_id'         => $this->user_id, 
            'name'            => $this->name,
            'value'           => $this->value,
            'provider'        => $this->provider,
            'account_name'    => $this->account_name,
            'account_number'  => $this->account_number,
            'expiry_date'     => $this->expiry_date,
            'billing_address' => $this->billing_address,
            'is_default'      => $this->is_default,
            'order_number'    => $this->order_number,
            'status'          => $this->status,
            'show_online_payment' => $this->show_online_payment,
            'show_table_order_payment' => $this->show_table_order_payment,
            'is_pos_static_qr_code_payment' => $this->is_pos_static_qr_code_payment,
            'is_pos_bank_integrate_payment' => $this->is_pos_bank_integrate_payment,
            'short_description' => $this->short_description,
            'pos_static_qr_code_thumb' => $this->pos_static_qr_code_thumb,
            'pos_static_qr_code_cover' => $this->pos_static_qr_code_cover,
            'pos_static_qr_code_preview' => $this->pos_static_qr_code_preview,
            'logo_thumb' => $this->logo_thumb,
            'logo_cover' => $this->logo_cover,
            'logo_preview' => $this->logo_preview,
            'supported_currencies' => $this->supportedCurrencies->map(function($currency) {
                return [
                    'id' => $currency->id,
                    'name' => $currency->name,
                    'code' => $currency->code,
                    'symbol' => $currency->symbol,
                    'exchange_rate' => $currency->exchange_rate,
                ];
            }),
        ];
    }
}
