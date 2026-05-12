<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class OrderStatusResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request): array
    {
        return [
            "id"           => $this->id,
            "branch_id"    => $this->branch_id,
            "status_code"  => $this->status_code,
            "name"         => $this->name,
            "name_kh"      => $this->name_kh,
            "name_cn"      => $this->name_cn,
            "name_en"      => $this->name_en,
            "status_order" => $this->status_order,
            "status"       => $this->status,
        ];
    }
}
