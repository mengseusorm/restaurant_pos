<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class RoomResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'             => $this->id,
            'name'           => $this->name,
            'branch_id'      => $this->branch_id,
            'branch'         => $this->branch ? [
                'id'   => $this->branch->id,
                'name' => $this->branch->name,
            ] : null,
            'status'         => $this->status,
            'derived_status' => $this->when(
                $this->relationLoaded('beds'),
                fn() => $this->derived_status
            ),
            'beds'           => $this->when(
                $this->relationLoaded('beds'),
                fn() => $this->beds->map(fn($bed) => [
                    'id'     => $bed->id,
                    'name'   => $bed->name,
                    'status' => $bed->status,
                ])->values()
            ),
            'qr_code_token'  => $this->qr_code_token,
            'created_at'     => $this->created_at,
            'updated_at'     => $this->updated_at,
        ];
    }
}
