<?php

namespace App\Http\Resources;

use App\Libraries\AppLibrary;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentMethodReportResource extends JsonResource
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
            "order_currency"               => $this->order_currency,
            "payment_method"            => $this->payment_method,
            "payment_method_name"       => $this->payment_method_name,
            "total_orders"              => $this->total_orders,

            "total"                     => AppLibrary::flatAmountFormat($this->total),
            "total_tax"                 => AppLibrary::flatAmountFormat($this->total_tax),
            "total_with_tax"            => AppLibrary::flatAmountFormat($this->total + $this->total_tax),
            // "total_items"              => $this->total_items,
            // "total_price"              => AppLibrary::flatAmountFormat($this->total_price), 
            // "total_currency_price"     => AppLibrary::flatAmountFormat($this->total_price), 
            // "total_tax_currency_price" => AppLibrary::flatAmountFormat($this->total_tax),  
            // "total_amount_price"       => AppLibrary::flatAmountFormat($this->total_price + $this->total_tax),  
        ];
    }
}
