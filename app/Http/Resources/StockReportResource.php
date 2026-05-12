<?php

namespace App\Http\Resources;

use App\Libraries\AppLibrary;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Log;

class StockReportResource extends JsonResource
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
            'branch_id'       => $this->branch_id,
            'branch_name'     => $this->branch_name,
            'stock_id'        => $this->stock_id,
            'stock_name'      => $this->stock_name,
            'item_id'         => $this->item_id,
            'item_name'       => $this->item_name,
            'item_barcode'    => $this->item_barcode,
            'start_stock'     => AppLibrary::datetime($this->created_at),
            'stock_in'        => $this->stock_in,
            'stock_out'       => $this->stock_out,
            'remaining_stock' => $this->remaining_stock,
            'current_remain_stock' => $this->current_remain_stock
        ];
    }
}
