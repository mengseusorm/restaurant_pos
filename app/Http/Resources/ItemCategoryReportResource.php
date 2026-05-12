<?php

namespace App\Http\Resources;

use App\Libraries\AppLibrary;
use Illuminate\Http\Resources\Json\JsonResource;

class ItemCategoryReportResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            "order_currency"               => $this->order_currency,
            "category_name"            => $this->category_name,
            "total_items"              => $this->total_items,
            "total_price"              => AppLibrary::flatAmountFormat($this->total_price),
            "total_orders"             => $this->total_orders,
            "total_currency_price"     => AppLibrary::flatAmountFormat($this->total_price),
            "total_tax_currency_price" => AppLibrary::flatAmountFormat($this->total_tax),
            "total_amount_price"       => AppLibrary::flatAmountFormat($this->total_price + $this->total_tax),
            // "currency_code"            => $this->currency_code
        ];
    }
}
