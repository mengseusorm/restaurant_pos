<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class BedResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'         => $this->id,
            'branch_id'  => $this->branch_id,
            'room_id'    => $this->room_id,
            'room'       => $this->room ? [
                'id'   => $this->room->id,
                'name' => $this->room->name,
            ] : null,
            'name'       => $this->name,
            'status'     => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
