<?php

namespace App\Http\Resources;


use App\Enums\Status;
use App\Libraries\AppLibrary;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class NormalItemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        $price = $this->price;
        return [
            "id"             => $this->id,
            "name"           => $this->name,
            "name_kh"          => $this->name_kh,
            "name_cn"          => $this->name_cn,
            "name_en"          => $this->name_en,
            "item_code"        => $this->item_code,
            
            "slug"           => $this->slug,
            "branch_id"      => $this->branch_id,
            "flat_price"     => AppLibrary::flatAmountFormat($this->price),
            "convert_price"  => AppLibrary::convertAmountFormat($this->price),
            "currency_price" => AppLibrary::currencyAmountFormat($this->price),
            "price"          => $this->price,
            "item_type"      => $this->item_type,
            "status"         => $this->status,
            "description"    => $this->description === null ? '' : $this->description,
            "caution"        => $this->caution === null ? '' : $this->caution,
            "thumb"          => $this->thumb,
            "cover"          => $this->cover,
            "preview"        => $this->preview,
            "variations"     => $this->variations->groupBy('item_attribute_id'),
            "itemAttributes" => ItemAttributeResource::collection($this->itemAttributeList($this->variations)),
            "extras"         => ItemExtraResource::collection($this->extras),
            "addons"         => ItemAddonResource::collection($this->addons),
            "offer"          => SimpleOfferResource::collection(
                $this->offer->filter(function ($offer) use ($price) {
                    if (Carbon::now()->between(
                            $offer->start_date,
                            $offer->end_date
                        ) && $offer->status === Status::ACTIVE) {
                        $offer->flat_price     = AppLibrary::flatAmountFormat($price - ($price / 100 * $offer->amount));
                        $offer->convert_price  = AppLibrary::convertAmountFormat(
                            $price - ($price / 100 * $offer->amount)
                        );
                        $offer->currency_price = AppLibrary::currencyAmountFormat(
                            $price - ($price / 100 * $offer->amount)
                        );
                        return $offer;
                    }
                })
            )
        ];
    }

    private function itemAttributeList($variations
    ) : \Vanilla\Support\Collection | \IlluminateAgnostic\Str\Support\Collection | \IlluminateAgnostic\StrAgnostic\Str\Support\Collection | \IlluminateAgnostic\Collection\Support\Collection | \IlluminateAgnostic\ArrAgnostic\Arr\Support\Collection | \Illuminate\Support\Collection | \IlluminateAgnostic\Arr\Support\Collection {
        $array = [];
        foreach ($variations as $b) {
            if (!isset($array[$b->itemAttribute->id])) {
                $array[$b->itemAttribute->id] = (object)[
                    'id'                  => $b->itemAttribute->id,
                    'name'                => $b->itemAttribute->name,
                    'status'              => $b->itemAttribute->status,
                    'require_input_price' => $b->itemAttribute->require_input_price
                ];
            }
        }
        return collect($array);
    }
}
