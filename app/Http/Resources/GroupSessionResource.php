<?php

namespace App\Http\Resources;

use App\Libraries\AppLibrary;
use Illuminate\Http\Resources\Json\JsonResource;

class GroupSessionResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'                => $this->id,
            'code'              => $this->code,
            'status'            => $this->status,
            'arrival_time'      => AppLibrary::datetime($this->arrival_time),
            'end_time'          => AppLibrary::datetime($this->end_time),
            'total_guests'      => $this->total_guests,
            'is_group_checkout' => (bool) $this->is_group_checkout,
            'notes'             => $this->notes,
            'sub_sessions'      => SubSessionResource::collection($this->whenLoaded('subSessions')),
            'sub_session_count' => $this->whenLoaded(
                'subSessions',
                fn() => $this->subSessions->count()
            ),
            'total_amount'      => $this->whenLoaded(
                'subSessions',
                fn() => (float) $this->subSessions->sum('subtotal')
            ),
            'orders'            => $this->whenLoaded('orders', fn() => $this->orders->map(fn($o) => [
                'id'             => $o->id,
                'order_serial_no'=> $o->order_serial_no,
                'customer_name'  => $o->customer_name,
                'total'          => (float) $o->total,
                'paid_amount'    => (float) ($o->paid_amount ?? 0),
                'balance_due'    => (float) ($o->balance_due ?? $o->total),
                'payment_status' => $o->payment_status,
            ])),
            'created_at'        => AppLibrary::datetime($this->created_at),
            'updated_at'        => AppLibrary::datetime($this->updated_at),
        ];
    }
}
