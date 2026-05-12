<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderTypeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'branch_id' => $this->branch_id,
            'type_code' => $this->type_code,
            'name' => $this->name,
            'name_kh' => $this->name_kh,
            'name_cn' => $this->name_cn,
            'name_en' => $this->name_en,
            'type_order' => $this->type_order,
            'status' => $this->status,
        ];
    }
}
