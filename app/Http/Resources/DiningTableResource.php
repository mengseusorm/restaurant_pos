<?php

namespace App\Http\Resources;


use Illuminate\Http\Resources\Json\JsonResource;

class DiningTableResource extends JsonResource
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
            "id" => $this->id,
            "name" => $this->name,
            "slug" => $this->slug,
            "size" => $this->size,
            "qr_code" => $this->qr_code ? asset($this->qr_code) : null,
            "branch_id" => $this->branch_id,
            "branch_name" => optional($this->branch)->name,
            "status" => $this->status,
            "qr" => $this->qr,
            "branch_address" => $this->branch->address,
            "branch_phone" => $this->branch->phone,
            'current_order_id' => $this->current_order_id,
            'floor_plan_group_id' => $this->floor_plan_group_id,
            'position_x' => $this->position_x,
            'position_y' => $this->position_y,
            'width' => $this->width,
            'height' => $this->height,
            'rotation' => $this->rotation,
            'shape' => $this->shape,
            'color' => $this->color,
            'current_guests' => $this->current_guests,
            'is_occupied' => $this->is_occupied,
            'occupancy_rate' => $this->occupancy_rate,
            'table_photo' => $this->table_photo,
            'table_thumb' => $this->table_thumb,
            'floor_plan_group' => $this->whenLoaded('floorPlanGroup'),
            'orders' => $this->whenLoaded('orders')
        ];
    }
}
