<?php

namespace App\Http\Resources;

use App\Libraries\AppLibrary;
use Illuminate\Http\Resources\Json\JsonResource;

class SaleReportResource extends JsonResource
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
            'id'                           => $this->id,
            "user"                         => $this->user,
            'order_serial_no'              => $this->order_serial_no,
            'invoice_number'               => $this->invoice_number,
            'order_type'                   => $this->order_type,
            'order_datetime'               => AppLibrary::datetime($this->order_datetime),
            'created_at'                   => AppLibrary::datetime($this->created_at), 
            "discount_amount_price"        => AppLibrary::flatAmountFormat($this->discount),
            "delivery_charge_amount_price" => AppLibrary::flatAmountFormat($this->delivery_charge), 
            'payment_method'            => $this->paymentMethod,
            'payment_status'               => $this->payment_status,
            'transaction'                  => new TransactionResource($this->transaction), 
            'branch'                        => $this->branch ? new BranchMinimalResource($this->branch) : null, 
            "total_currency_price"         => AppLibrary::flatAmountFormat($this->total),
            "total_tax_currency_price"     => AppLibrary::flatAmountFormat($this->total_tax),
            "total_amount_price"           => AppLibrary::flatAmountFormat($this->total + $this->total_tax), 
        ];
    }
}
