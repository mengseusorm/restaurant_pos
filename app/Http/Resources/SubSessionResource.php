<?php

namespace App\Http\Resources;

use App\Libraries\AppLibrary;
use Illuminate\Http\Resources\Json\JsonResource;

class SubSessionResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'               => $this->id,
            'group_session_id' => $this->group_session_id,
            'guest_name'       => $this->guest_name,
            'phone'            => $this->phone,
            'status'           => $this->status,
            'start_time'       => AppLibrary::datetime($this->start_time),
            'end_time'         => AppLibrary::datetime($this->end_time),
            'date_format'      => env('DATE_FORMAT', 'd-m-Y'),
            'time_format'      => env('TIME_FORMAT', 'h:i A'),
            'is_checked_out'   => (bool) $this->is_checked_out,
            'share_group_bill' => (bool) $this->share_group_bill,
            'notes'            => $this->notes,
            'order_id'         => $this->order_id,
            'order_payment_status' => (function () {
                // Direct order on the sub-session
                if ($this->relationLoaded('order') && $this->order) {
                    return $this->order->payment_status;
                }
                if ($this->order_id) {
                    return $this->order?->payment_status;
                }
                // Group-bill: order lives on the group session
                if ($this->relationLoaded('groupSession') && $this->groupSession
                    && $this->groupSession->relationLoaded('orders')
                    && $this->groupSession->orders->isNotEmpty()) {
                    return $this->groupSession->orders->first()->payment_status;
                }
                return null;
            })(),
            'resolved_order_id' => (function () {
                if ($this->order_id) {
                    return $this->order_id;
                }
                if ($this->relationLoaded('groupSession') && $this->groupSession
                    && $this->groupSession->relationLoaded('orders')
                    && $this->groupSession->orders->isNotEmpty()) {
                    return $this->groupSession->orders->first()->id;
                }
                return null;
            })(),
            'session_items'    => SessionItemResource::collection($this->whenLoaded('sessionItems')),
            'subtotal'         => $this->whenLoaded(
                'sessionItems',
                fn() => (float) $this->sessionItems->sum('final_price')
            ),
            'created_at'       => AppLibrary::datetime($this->created_at),
            'updated_at'       => AppLibrary::datetime($this->updated_at),
        ];
    }
}
