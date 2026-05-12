<?php

namespace App\Http\Resources;

use App\Libraries\AppLibrary;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderTypeReportResource extends JsonResource
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
            "order_currency"   => $this->order_currency,
            "order_type"       => $this->order_type,
            "total_order_type" => $this->total_order_type,
            "total_tax"        => AppLibrary::flatAmountFormat($this->total_tax),
            "total_price"      => AppLibrary::flatAmountFormat($this->total_price),
            "total_with_tax"   => AppLibrary::flatAmountFormat($this->total_price + $this->total_tax),

        ];
    }
}
