<?php

namespace App\Http\Resources;

use App\Libraries\AppLibrary;
use Illuminate\Http\Resources\Json\JsonResource;

class ItemReportResource extends JsonResource
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
            "id"                           => $this->id,
            "name"                         => $this->name,
            "category_name"                => $this->category_name,
            "category_id"                  => $this->category_id,
            "total_ordered_qty"            => $this->total_ordered_qty,
            "total_tax"                    => AppLibrary::flatAmountFormat($this->total_tax),
            "order_count"                  => $this->order_count,
            "order_created_at"             => $this->order_created_at,
            "current_total_price"          => AppLibrary::flatAmountFormat($this->current_total_price),
            "current_total_price_with_tax" => AppLibrary::flatAmountFormat($this->current_total_price + $this->total_tax),
            "total_amount"                 => AppLibrary::flatAmountFormat($this->total_amount),
            'currency_code'                => $this->currency_code
        ];
    }
}
