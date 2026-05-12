<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PaywayTransactionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray($request): array
    {
        return [
            'id'                   => $this->id,
            'tran_id'              => $this->tran_id,
            'order_id'             => $this->order_id,
            'order_serial_no'      => $this->order?->order_serial_no ?? 'N/A',
            'branch_id'            => $this->branch_id,
            'branch_name'          => $this->branch?->name ?? 'N/A',
            'payment_method_id'    => $this->payment_method_id,
            'payment_method_name'  => $this->paymentMethod?->name ?? 'N/A',
            'amount'               => number_format((float)$this->amount, 2, '.', ''),
            'currency'             => $this->currency,
            'payment_status_code'  => $this->payment_status_code,
            'payment_status'       => $this->payment_status ?? 'PENDING',
            'payment_amount'       => $this->payment_amount ? number_format((float)$this->payment_amount, 2, '.', '') : null,
            'payment_currency'     => $this->payment_currency,
            'apv'                  => $this->apv,
            'transaction_date'     => $this->transaction_date ? $this->transaction_date->format('Y-m-d H:i:s') : null,
            'created_at'           => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at'           => $this->updated_at->format('Y-m-d H:i:s'),
            
            // Include related models when loaded
            'order'                => $this->when($this->relationLoaded('order'), function () {
                return [
                    'id'                    => $this->order->id,
                    'order_serial_no'       => $this->order->order_serial_no,
                    'invoice_number'        => $this->order->invoice_number,
                    'token'                 => $this->order->token,
                    'total'                 => number_format((float)$this->order->total, 2, '.', ''),
                    'currency'              => $this->order->currency,
                    'status'                => $this->order->status,
                    'payment_status'        => $this->order->payment_status,
                    'order_type'            => $this->order->order_type,
                    'order_datetime'        => $this->order->order_datetime,
                    'customer_name'         => $this->order->customer_name,
                    'customer_phone_number' => $this->order->customer_phone_number,
                    'customer_address'      => $this->order->customer_address,
                ];
            }),
            
            'branch'               => $this->when($this->relationLoaded('branch'), function () {
                return [
                    'id'    => $this->branch->id,
                    'name'  => $this->branch->name,
                    'email' => $this->branch->email ?? null,
                    'phone' => $this->branch->phone ?? null,
                ];
            }),
            
            'payment_method'       => $this->when($this->relationLoaded('paymentMethod'), function () {
                return [
                    'id'       => $this->paymentMethod->id,
                    'name'     => $this->paymentMethod->name,
                    'provider' => $this->paymentMethod->provider ?? null,
                ];
            }),
            
            // Include transaction relationship when loaded
            'transaction'          => $this->when($this->relationLoaded('transaction'), function () {
                return $this->transaction ? [
                    'id'                     => $this->transaction->id,
                    'transaction_no'         => $this->transaction->transaction_no,
                    'amount'                 => number_format((float)$this->transaction->amount, 2, '.', ''),
                    'currency'               => $this->transaction->currency,
                    'type'                   => $this->transaction->type,
                    'sign'                   => $this->transaction->sign ?? null,
                    'change_amount'          => $this->transaction->change_amount ? number_format((float)$this->transaction->change_amount, 2, '.', '') : null,
                    'change_currency'        => $this->transaction->change_currency ?? null,
                ] : null;
            }),
        ];
    }
}
