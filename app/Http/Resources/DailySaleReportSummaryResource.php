<?php

namespace App\Http\Resources;

use App\Libraries\AppLibrary;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class DailySaleReportSummaryResource extends JsonResource
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
            'users'                 => Auth::user()->name,
            'total_invoices'        => $this->total_invoices,
            'sale_items'            => $this->sale_items_by_printer,
            'total_revenue'         => AppLibrary::flatAmountFormat($this->total_revenue),
            'total_discount'        => AppLibrary::flatAmountFormat($this->total_discount),
            'void_invoice'          => $this->void_invoice,
            'deleted_orders'        => $this->deleted_orders,
            'payment_methods'       => $this->payment_methods,
            'deleted_order_items'   => $this->deleted_order_items,
        ];
    }
}
