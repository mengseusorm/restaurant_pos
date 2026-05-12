<?php

namespace App\Http\Resources;

use AWS\CRT\Log;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Log as FacadesLog;

class PrintLabelResource extends JsonResource
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
            'name'              => $this->name,
            'show_company_name' => $this->show_company_name,
            'show_branch_name' => $this->show_branch_name,
            'show_phone_number' => $this->show_phone_number,
            'show_order_number' => $this->show_order_number,
            'show_order_number_barcode' => $this->show_order_number_barcode,
            'show_order_qr_code' => $this->show_order_qr_code,
            'show_item' => $this->show_item,
            'show_item_qty' => $this->show_item_qty,
            'show_item_price' => $this->show_item_price,
            'show_customer_name' => $this->show_customer_name,
            'show_customer_phone_number' => $this->show_customer_phone_number,
            'show_delivery_address' => $this->show_delivery_address,
            'show_payment_status' => $this->show_payment_status,
            'show_payment_qr_code' => $this->show_payment_qr_code,
            'show_payment_method' => $this->show_payment_method,
            'print_qty' => $this->print_qty,
            'label_title' => $this->label_title,
            'label_width' => $this->label_width,
            'label_height' => $this->label_height,
            'separate_item' => $this->separate_item,
            'separate_qty' => $this->separate_qty,
            'label_style_custom' => $this->label_style_custom,
        ];
    }
}
