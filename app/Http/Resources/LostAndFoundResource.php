<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class LostAndFoundResource extends JsonResource
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
            'id'                => $this->id,
            'id' => $this->id,
            'item_name' => $this->item_name,
            'found_date' => $this->found_date,
            'found_by' => $this->found_by,
            'found_location' => $this->found_location,
            'customer_name'     => $this->customer_name,
            'customer_phone'    => $this->customer_phone,
            'customer_email'    => $this->customer_email,
            'status'            => $this->status,
            'claimed_by'        => $this->claimed_by,
            'claimed_date'      => $this->claimed_date,
            'notes'             => $this->notes,
            'branch_id'         => $this->branch_id,
            'branch'            => $this->branch ? [
                'id'   => $this->branch->id,
                'name' => $this->branch->name
            ] : null,
            'created_by'        => $this->created_by,
            'created_by_user'   => $this->createdBy ? [
                'id'   => $this->createdBy->id,
                'name' => $this->createdBy->name
            ] : null,
            'storage_location'  => $this->storage_location,
            'disposal_date'     => $this->disposal_date,
            'thumb'             => $this->thumb,
            'cover'             => $this->cover,
            'preview'           => $this->preview,
            'created_at'        => $this->created_at,
            'updated_at'        => $this->updated_at
        ];
    }
}
