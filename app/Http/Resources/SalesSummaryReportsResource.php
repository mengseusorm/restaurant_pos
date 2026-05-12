<?php

namespace App\Http\Resources;


use App\Libraries\AppLibrary;
use Illuminate\Http\Resources\Json\JsonResource;

class SalesSummaryReportsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request) : array
    {
        return [
            'id'                           => $this->id,
            'order_serial_no'              => $this->order_serial_no,
            'order_type'                   => $this->order_type,
            'order_datetime'               => AppLibrary::datetime($this->order_datetime),
            'created_at'                   => $this->created_at,
            "discount_amount_price"        => AppLibrary::flatAmountFormat($this->discount),
            "delivery_charge_amount_price" => AppLibrary::flatAmountFormat($this->delivery_charge),
            'payment_method'               => $this->payment_method,
            'payment_method_id'            => $this->paymentMethod,
            'payment_status'               => $this->payment_status,
            'transaction'                  => new TransactionResource($this->transaction),
            'pos_payment_method'           => $this->pos_payment_method,
            'pos_payment_method_name'      => $this->posPaymentMethod->name ?? null,
            'pos_payment_method_id'        => $this->posPaymentMethod->id ?? null,
            'branch'                       => $this->branch ? new BranchMinimalResource($this->branch) : null,
            'branch_id'                    => $this->branch_id,
            "total_currency_price"         => AppLibrary::flatAmountFormat($this->total),
            "total_tax_currency_price"     => AppLibrary::flatAmountFormat($this->total_tax),
            "total_amount_price"           => AppLibrary::flatAmountFormat($this->total + $this->total_tax),
            "user"                         => $this->user,
            "order_user_id"                => $this->order_user_id,
            "orderUser"                    => $this->orderUser,
            "source"                       => $this->source, 
        ];
    }
}
