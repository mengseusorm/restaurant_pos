<?php

namespace App\Http\Resources;

use App\Libraries\AppLibrary;
use Illuminate\Http\Resources\Json\JsonResource;

class ItemAttributeVariationResource extends JsonResource
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
            'id'                => $this->id,
            'item_attribute_id' => $this->item_attribute_id,
            'item_attribute'    => $this->itemAttribute ? [
                'id'   => $this->itemAttribute->id,
                'name' => $this->itemAttribute->name,
                'require_input_price' => $this->itemAttribute->require_input_price,
            ] : null,
            'name'              => $this->name,
            'price'             => AppLibrary::flatAmountFormat($this->price),
            'currency_price'    => $this->currency_price,
            'convert_price'     => $this->convert_price,
            'flat_price'        => $this->flat_price,
            'caution'           => $this->caution,
            'status'            => $this->status
        ];
    }
}
