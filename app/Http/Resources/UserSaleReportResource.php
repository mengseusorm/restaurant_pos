<?php

namespace App\Http\Resources;

use App\Libraries\AppLibrary;
use Illuminate\Http\Resources\Json\JsonResource;

class UserSaleReportResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request): array
    {
        return [
            "order_currency"               => $this->order_currency,
            'user_id'                    => $this->user_id,
            'user_name'                  => $this->user_name,

            "total_orders"              => $this->total_orders,

            "total"                     => AppLibrary::flatAmountFormat($this->total),
            "total_tax"                 => AppLibrary::flatAmountFormat($this->total_tax),
            "total_with_tax"            => AppLibrary::flatAmountFormat($this->total + $this->total_tax),
            
            // 'total_orders'               => $this->total_orders,
            // 'total_amount'               => AppLibrary::flatAmountFormat($this->total_amount),
            // 'total_tax'                  => AppLibrary::flatAmountFormat($this->total_tax),
            // 'total_amount_with_tax'      => AppLibrary::flatAmountFormat($this->total_amount_with_tax),
            // 'total_currency_amount'      => AppLibrary::currencyAmountFormat($this->total_amount),
            // 'total_tax_currency_amount'  => AppLibrary::currencyAmountFormat($this->total_tax),
            // 'total_amount_with_tax_currency' => AppLibrary::currencyAmountFormat($this->total_amount_with_tax),
        ];
    }
}
