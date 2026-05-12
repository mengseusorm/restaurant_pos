<?php

namespace App\Http\Resources;

use App\Libraries\AppLibrary;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Log;

class ItemDetailReportResource extends JsonResource
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
            'id'                  => $this->id ?? '',
            'order_no'            => $this->order_serial_no ?? '',
            'invoice_number'      => $this->invoice_number ?? '',
            'invoice_date'        => $this->order_date ? \Carbon\Carbon::parse($this->order_date)->format('m/d/Y H:i') : '',
            'table_no'            => $this->table_names ?? '',
            'item_code'           => $this->item_code ?? '--',
            'name'                => $this->item_name ?? '',
            'quantity'            => $this->quantity ?? 0,
            'price'               => AppLibrary::flatAmountFormat($this->price ?? 0),
            'sub_total'           => AppLibrary::flatAmountFormat($this->sub_total),
            'discount_percentage' => $this->discount_percentage,
            'discount'            => AppLibrary::flatAmountFormat($this->discount),
            'total'               => AppLibrary::flatAmountFormat($this->total_price ?? 0),
            'total_amount'        => AppLibrary::flatAmountFormat($this->total_price ?? 0),
            'change_amount'       => AppLibrary::flatAmountFormat($this->change_amount ?? 0),
            'received_dollar'     => AppLibrary::flatAmountFormat($this->dollar_amount ?? 0),
            'received_riel'       => AppLibrary::flatAmountFormat($this->riel_amount ?? 0),
            'payment'             => $this->payment_method ?? 'N/A',
            'remark'              => $this->instruction ?? '',
            'currency'            => $this->currency ?? '',
            'customer_name'       => $this->customer_name ?? '',
            'order_type'          => $this->order_type ?? '',
            'branch_name'         => $this->branch_name ?? '',
            'change'              => is_string($this->change) ? json_decode($this->change, true) : ($this->change ?? 0),  
        ];
    }
}
