<?php

namespace App\Http\Resources;

use App\Libraries\AppLibrary;
use Illuminate\Http\Resources\Json\JsonResource;

class BranchSaleReportResource extends JsonResource
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
            "order_currency"                => $this->order_currency,
            "branch_name"                   => $this->branch_name,
            "branch_id"                     => $this->branch_id, 
            "total_orders"                  => $this->total_orders, 
            "total"                         => AppLibrary::flatAmountFormat($this->total),
            "total_tax"                     => AppLibrary::flatAmountFormat($this->total_tax),
            "total_with_tax"                => AppLibrary::flatAmountFormat($this->total + $this->total_tax), 
        ];
    }
}
