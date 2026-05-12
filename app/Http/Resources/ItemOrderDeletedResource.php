<?php

namespace App\Http\Resources;

use App\Libraries\AppLibrary;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Log;

class ItemOrderDeletedResource extends JsonResource
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
            'order_id'              => $this->order_id,
            'order_serial_no'       => $this->order_serial_no,
            'delete_reason'         => $this->delete_reason,
            'branch_id'             => $this->branch_id,
            'item_name'             => $this->item->name ?? '',
            'quantity'              => $this->quantity,
            'discount'              => AppLibrary::flatAmountFormat($this->discount),
            'discount_percentage'   => $this->discount_percentage,
            'tax_name'              => $this->tax_name,
            'tax_rate'              => $this->tax_rate,
            'tax_type'              => $this->tax_type,
            'tax_amount'            => AppLibrary::flatAmountFormat($this->tax_amount), 
            'total_convert_price'   => AppLibrary::convertAmountFormat($this->total_price),
            'total_currency_price'  => AppLibrary::branchCurrencyAmountFormat($this->total_price,$this->branch_id),
            'price'                 => AppLibrary::flatAmountFormat($this->price),
            'item_variations'       => $this->item_variations,
            'item_extras'           => $this->item_extras,
            'item_variation_total'  => AppLibrary::flatAmountFormat($this->item_variation_total),
            'item_extra_total'      => AppLibrary::flatAmountFormat($this->item_extra_total),
            'total_price'           => AppLibrary::flatAmountFormat($this->total_price),
            'instruction'           => $this->instruction,
            'order_times'           => $this->order_times,
            'order_item_status'     => $this->order_item_status,
            'reasons'               => $this->reasons,  
            'order_created_at'      => AppLibrary::datetime($this->order_created_at),
            'order_updated_at'      => AppLibrary::datetime($this->order_updated_at), 
        ];
    }
}
