<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PaymentMethodMinimalResource extends JsonResource
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
            // 'user_id'         => $this->user_id, 
            'name'            => $this->name,
            // 'value'           => $this->value,
            'provider'        => $this->provider,
            // 'account_name'    => $this->account_name,
            // 'account_number'  => $this->account_number,
            // 'expiry_date'     => $this->expiry_date,
            // 'billing_address' => $this->billing_address,
            // 'is_default'      => $this->is_default,
            // 'order_number'    => $this->order_number,
            // 'status'          => $this->status,
            // 'show_online_payment' => $this->show_online_payment,
        ];
    }
}
