<?php

namespace App\Http\Resources;

use App\Libraries\AppLibrary;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderDeletedResource extends JsonResource
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
            'id'                       => $this->id,
            'order_serial_no'          => $this->order_serial_no,
            'invoice_number'           => $this->invoice_number,
            'token'                    => $this->token,
            'waiting_number'           => $this->waiting_number,
            'user'                     => $this->user->name ?? 'N/A',
            'branch'                   => $this->branch->name ?? 'N/A',
            'customer_name'            => $this->customer_name ?? 'Guest',
            'subtotal'                 => AppLibrary::flatAmountFormat($this->subtotal),
            'discount'                 => AppLibrary::flatAmountFormat($this->discount),
            'total_tax'                => AppLibrary::flatAmountFormat($this->total_tax), 
            'total'                    => AppLibrary::flatAmountFormat($this->total), 
            'total_amount_price'       => AppLibrary::flatAmountFormat($this->total + $this->total_tax),
            'order_type'               => $this->order_type,
            'order_datetime'           => AppLibrary::datetime($this->order_datetime),
            'payment_method'           => $this->paymentMethod->name ?? 'N/A',
            'payment_status'           => $this->payment_status,
            'status'                   => $this->status,
            'dining_table_id'          => $this->dining_table_id,
            'source'                   => $this->source,
            'dining_table'             => $this->dining_table ?? [],
            'deleted_reason'           => $this->deleted_reason ?? null,
            'deleted_at'               => $this->deleted_at,
            'deleted_by'               => $this->deleted_by,
        ];
    }
}
