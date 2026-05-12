<?php

namespace App\Http\Resources;

use App\Enums\TaxType;
use App\Models\Currency;
use App\Libraries\AppLibrary;
use App\Models\kitchenPrinter;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Log;
use Smartisan\Settings\Facades\Settings;

class OrderDiningResource extends JsonResource
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
            // 'branch_id'                        => $this->branch_id,
            'dining_table_id'                  => $this->diningTable?->id,
            'dining_table_name'            => $this->diningTable?->name,
            'dining_table'                     => $this->diningTable,
        ]; 
    }
}
