<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ExchangeRateResource extends JsonResource
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
            'id' => $this->id,
            'base_currency' => $this->base_currency,
            'target_currency' => $this->target_currency,
            'rate' => $this->rate,
            'effective_at' => $this->effective_at ? $this->effective_at->format('Y-m-d') : null,
            'effective_at_formatted' => $this->effective_at ? $this->effective_at->format('Y-m-d H:i:s') : null,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            
            // Include currency details if loaded
            'base_currency_details' => $this->whenLoaded('baseCurrencyModel', function () {
                return [
                    'name' => $this->baseCurrencyModel->name,
                    'symbol' => $this->baseCurrencyModel->symbol,
                    'code' => $this->baseCurrencyModel->code,
                ];
            }),
            'target_currency_details' => $this->whenLoaded('targetCurrencyModel', function () {
                return [
                    'name' => $this->targetCurrencyModel->name,
                    'symbol' => $this->targetCurrencyModel->symbol,
                    'code' => $this->targetCurrencyModel->code,
                ];
            }),
        ];
    }
}
