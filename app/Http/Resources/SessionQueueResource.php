<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SessionQueueResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'            => $this->id,
            'branch_id'     => $this->branch_id,
            'room_id'       => $this->room_id,
            'room'          => $this->room ? [
                'id'   => $this->room->id,
                'name' => $this->room->name,
            ] : null,
            'service_id'    => $this->service_id,
            'service'       => $this->service ? [
                'id'    => $this->service->id,
                'name'  => $this->service->name,
                'price' => $this->service->price,
            ] : null,
            'therapist_id'  => $this->therapist_id,
            'therapist'     => $this->therapist ? [
                'id'    => $this->therapist->id,
                'name'  => $this->therapist->name,
                'phone' => $this->therapist->phone,
            ] : null,
            'customer_name'  => $this->customer_name,
            'customer_phone' => $this->customer_phone,
            'notes'          => $this->notes,
            'position'       => $this->position,
            'status'         => $this->status,
            'created_at'     => $this->created_at,
        ];
    }
}
