<?php

namespace App\Http\Resources;

use App\Libraries\AppLibrary;
// use App\Models\ItemStock; 
use Illuminate\Http\Resources\Json\JsonResource;

class StockRecordResource extends JsonResource
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
            'id'                => $this->id, 
            'item_id'           => $this->item,
            'stock_id'          => $this->stock,  
            'user_id'           => $this->user,
            'order_id'          => $this->order_id,
            'quantity'          => $this->quantity,
            'record_type'       => $this->record_type,
            'transferType'      => $this->transferType,
            'to_warehouse'   => $this->towarehouse,
            'from_warehouse' => $this->fromwarehouse,
            'created_at'        => AppLibrary::datetime($this->created_at), 
        ];
    }

}
