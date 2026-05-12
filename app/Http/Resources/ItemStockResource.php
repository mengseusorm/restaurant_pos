<?php

namespace App\Http\Resources;

use App\Libraries\AppLibrary;
use Illuminate\Http\Resources\Json\JsonResource;

class ItemStockResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request) : array
    {
        return [
            'id'                => $this->id,
            'name'              => $this->name,
            'description'       => $this->description, 
            'branch'          => $this->branch ? new BranchMinimalResource($this->branch) : null,
            'branch_id'        => $this->branch_id,
        ];
    }
}
