<?php

namespace App\Http\Resources;

use App\Libraries\AppLibrary;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Log;

class OrderSourceReportResource extends JsonResource
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
            "order_currency"                => $this->order_currency,
            "source"                        => $this->source,
            "total_orders"                  => $this->total_orders, 
            "total"                         => AppLibrary::flatAmountFormat($this->total),
            "total_tax"                     => AppLibrary::flatAmountFormat($this->total_tax),
            "total_with_tax"                => AppLibrary::flatAmountFormat($this->total + $this->total_tax),
        ];
    }
}
