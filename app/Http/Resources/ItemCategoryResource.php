<?php

namespace App\Http\Resources;


use Illuminate\Http\Resources\Json\JsonResource;

class ItemCategoryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request) : array
    {
        return [
            'id'          => $this->id,
            'name'        => $this->name,
            'name_kh'     => $this->name_kh,
            'name_cn'     => $this->name_cn,
            'name_en'     => $this->name_en,
            'item_category_code' => $this->item_category_code,
            'slug'        => $this->slug,
            'description' => $this->description === null ? '' : $this->description,
            'status'      => $this->status,
            'thumb'       => $this->thumb,
            'cover'       => $this->cover,
            'branch_id'   => $this->branch_id,
            'branch'      => new BranchMinimalResource($this->branch),
            'sort'        => $this->sort,
            'items_count' => $this->items_count,
        ];
    }
}
