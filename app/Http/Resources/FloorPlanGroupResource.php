<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class FloorPlanGroupResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'sort_order' => $this->sort_order,
            'status' => $this->status,
            'branch_id' => $this->branch_id,
            'floor_plan_photo' => $this->floor_plan_photo,
            'dining_tables' => DiningTableResource::collection($this->whenLoaded('diningTables')),
            'tables_count' => $this->dining_tables_count ?? $this->diningTables->count(),
            'occupied_tables_count' => $this->occupied_tables_count ?? $this->diningTables->where('current_order_id', '!=', null)->count(),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
