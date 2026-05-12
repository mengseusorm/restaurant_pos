<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CustomerBeverageStorageResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'storage_code' => $this->storage_code,
            'customer_name' => $this->customer_name,
            'customer_phone' => $this->customer_phone,
            'beverage_name' => $this->beverage_name,
            'quantity' => $this->quantity,
            'original_quantity' => $this->original_quantity,
            'unit' => $this->unit,
            'store_date' => $this->store_date,
            'expiry_date' => $this->expiry_date,
            'status' => $this->status,
            'storage_location' => $this->storage_location,
            'claimed_date' => $this->claimed_date,
            'disposed_date' => $this->disposed_date,
            'disposed_reason' => $this->disposed_reason,
            'notes' => $this->notes,
            'branch_id' => $this->branch_id,
            'branch' => $this->branch,
            'created_by' => $this->created_by,
            'created_by_user' => $this->createdBy,
            'thumb' => $this->thumb,
            'cover' => $this->cover,
            'preview' => $this->preview,
        ];
    }
}
