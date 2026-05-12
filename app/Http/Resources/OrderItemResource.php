<?php

namespace App\Http\Resources;

use App\Enums\TaxType;
use App\Models\Currency;
use App\Libraries\AppLibrary;
use App\Models\kitchenPrinter;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Log;
use Smartisan\Settings\Facades\Settings;

class OrderItemResource extends JsonResource
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
            'id'                               => $this->id,
            'order_id'                         => $this->order_id,
            'branch_id'                        => $this->branch_id,
            'item_id'                          => $this->orderItem?->id,
            'item_name'                        => trim(($this->orderItem?->name ?? '') . ' ' . ($this->order_item_custom_name ?? '')),
            'item_image'                       => $this->orderItem?->thumb,
            'quantity'                         => $this->quantity,
            'discount'                         => AppLibrary::flatAmountFormat($this->discount),
            'discount_currency'                => AppLibrary::branchCurrencyAmountFormat($this->discount,$this->branch),
            'discount_percentage'              => $this->discount_percentage,
            'price'                            => AppLibrary::flatAmountFormat($this->price),
            'price_currency'                   => AppLibrary::branchCurrencyAmountFormat($this->price,$this->branch),
            'item_variations'                  => json_decode($this->item_variations),
            'item_extras'                      => json_decode($this->item_extras),
            'item_variation_currency_total'    => AppLibrary::branchCurrencyAmountFormat($this->item_variation_total,$this->branch),
            'item_extra_currency_total'        => AppLibrary::branchCurrencyAmountFormat($this->item_extra_total,$this->branch),
            'total_convert_price'              => AppLibrary::convertAmountFormat($this->total_price),
            'total_currency_price'             => AppLibrary::branchCurrencyAmountFormat($this->total_price,$this->branch),
            'instruction'                      => $this->instruction,
            'tax_type'                         => $this->tax_type === TaxType::FIXED ? env('CURRENCY') : '%',
            'tax_rate'                         => $this->tax_rate,
            'tax_currency_rate'                => AppLibrary::flatAmountFormat($this->tax_rate),
            'tax_name'                         => $this->tax_name,
            'tax_currency_amount'              => AppLibrary::branchCurrencyAmountFormat($this->tax_amount,$this->branch),
            // 'total_without_tax_currency_price' => AppLibrary::branchCurrencyAmountFormat($this->total_price - $this->tax_amount,$this->branch),
            'total_without_tax_currency_price' => AppLibrary::branchCurrencyAmountFormat($this->total_price, $this->branch),
            'printers'                         => [kitchenPrinter::find($this->orderItem->kitchen_printer_id)], 
            // 'printers'                         => KitchenPrinterMinimalResource::collection([kitchenPrinter::find($this->orderItem->kitchen_printer_id)]?? collect()),
            'branch'                            => $this->branch ? new BranchMinimalResource($this->branch) : null,
            'branch_id'                         => $this->branch_id,
            'order_times'                      => $this->order_times,
            'created_at'                       => AppLibrary::datetime($this->created_at),    
        ]; 
    }
}
