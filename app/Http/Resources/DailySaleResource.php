<?php

namespace App\Http\Resources;

use App\Libraries\AppLibrary;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Log;

class DailySaleResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request) :  array
    {  
        return [
            "order_currency"    => $this->order_currency,
            "order_date" => AppLibrary::date($this->order_date), 
            "total_orders" => $this->total_orders,

            "total" => AppLibrary::flatAmountFormat($this->total),
            "total_tax" => AppLibrary::flatAmountFormat($this->total_tax),
            "total_with_tax" => AppLibrary::flatAmountFormat($this->total + $this->total_tax), 
        ];
    }
}
