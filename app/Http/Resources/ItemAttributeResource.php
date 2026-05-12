<?php

namespace App\Http\Resources;

use App\Libraries\AppLibrary;
use Illuminate\Http\Resources\Json\JsonResource;

class ItemAttributeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request) : array
    {
        $data = [
            'id'                  => $this->id,
            'name'                => $this->name,
            'status'              => $this->status,
            'require_input_price' => $this->require_input_price,
        ];

        // Only include variations if this is an Eloquent model with relationships
        if ($this->resource instanceof \Illuminate\Database\Eloquent\Model) {
            $data['variations'] = $this->whenLoaded('variations', function() {
                return $this->variations->map(function($variation) {
                    return [
                        'id' => $variation->id,
                        'name' => $variation->name,
                        'price' => AppLibrary::flatAmountFormat($variation->price),
                        'convert_price' => AppLibrary::flatAmountFormat($variation->convert_price),
                        'status' => $variation->status,
                    ];
                });
            }, []);
        } else {
            $data['variations'] = [];
        }

        return $data;
    }

}
