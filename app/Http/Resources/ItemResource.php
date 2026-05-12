<?php

namespace App\Http\Resources;


use App\Enums\Status;
use App\Libraries\AppLibrary;
use App\Models\kitchenPrinter;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Log;

class ItemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request): array
    {
        $price = $this->price; 
        $data = [
            "id"               => $this->id,
            "name"             => $this->name,
            "name_kh"          => $this->name_kh,
            "name_cn"          => $this->name_cn,
            "name_en"          => $this->name_en,
            "item_code"        => $this->item_code,
            "slug"             => $this->slug,
            "item_category_id" => $this->item_category_id,
            "tax_id"           => $this->tax_id,
            "flat_price"       => AppLibrary::flatAmountFormat($this->price),
            "convert_price"    => AppLibrary::convertAmountFormat($this->price),
            "currency_price"   => AppLibrary::currencyAmountFormat($this->price),   
            "branch_currency_price"   => $this->branch ? AppLibrary::branchCurrencyAmountFormat($this->price ?? 0, $this->branch) : null,
            // show tax
            "total_currency_price"     => AppLibrary::flatAmountFormat($this->price),
            "total_tax_currency_price" => AppLibrary::flatAmountFormat($this->total_tax), 
            "total_amount_price"       => AppLibrary::flatAmountFormat($this->price + $this->total_tax),
            // end

            "tax_name"        => $this->tax_name,
            "tax_rate"        => $this->tax_rate,
            "tax_type"        => $this->tax_type,
            "tax_amount"      => $this->tax_amount,
            "price_with_tax"  => $this->price_with_tax,

            "price"            => $this->price,
            "item_type"        => $this->item_type,
            "is_featured"      => $this->is_featured,
            "status"           => $this->status,
            "description"      => $this->description === null ? '' : $this->description,
            "caution"          => $this->caution === null ? '' : $this->caution,
            "order"            => $this->orders->count(),
            "thumb"            => $this->thumb,
            "cover"            => $this->cover,
            "preview"          => $this->preview,
            "category_name"    => optional($this->category)->name,
            "category"         => new ItemCategoryMinimalResource($this->category),
            "tax"              => new TaxMinimalResource($this->tax),
            "variations"       => $this->variations->groupBy('item_attribute_id'),
            "itemAttributes"   => ItemAttributeResource::collection($this->itemAttributeList($this->variations)),
            "extras"           => ItemExtraResource::collection($this->extras),
            "addons"           => ItemAddonResource::collection($this->addons),
            "offer"            => SimpleOfferResource::collection(
                $this->offer->filter(function ($offer) use ($price) {
                    if (Carbon::now()->between($offer->start_date, $offer->end_date) && $offer->status === Status::ACTIVE) {
                        $amount                = ($price - ($price / 100 * $offer->amount));
                        $offer->flat_price     = AppLibrary::flatAmountFormat($amount);
                        $offer->convert_price  = AppLibrary::convertAmountFormat($amount);
                        $offer->currency_price = AppLibrary::currencyAmountFormat($amount );
                        return $offer;
                    }
                })
            ),
            "kitchen_printer_id" => new KitchenPrinterMinimalResource($this->kitchenPrinter),
            "label_printer_id" => $this->label_printer_id,
            "label_printer" => new KitchenPrinterMinimalResource($this->labelPrinter),
            'total_ordered_qty'  => $this->total_ordered_qty,
            'branch'          => $this->branch ? new BranchMinimalResource($this->branch) : null,
            'branch_id'      => $this->branch_id,
            'barcode'            => $this->barcode, 
            'manage_stock'       => $this->manage_stock,
            'is_print_menu'      => $this->is_print_menu,
            'is_print_label'     => $this->is_print_label,
            'can_input_custom_name' => $this->can_input_custom_name,
            'can_input_custom_unit_price' => $this->can_input_custom_unit_price,
            'item_kind'                    => $this->item_kind ?? 1,
            'duration'                     => $this->duration,
            'created_at'                   => $this->created_at, 
        ]; 

        return $data;
    }

    private function itemAttributeList($variations)
    {
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