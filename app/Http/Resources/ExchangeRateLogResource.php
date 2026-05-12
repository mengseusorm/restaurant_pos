<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ExchangeRateLogResource extends JsonResource
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
            'source' => $this->source,
            'created_by' => $this->created_by,
            'created_at' => $this->created_at,
            
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
            
            // Include user details if loaded
            'creator_details' => $this->whenLoaded('creator', function () {
                return [
                    'id' => $this->creator->id,
                    'name' => $this->creator->name ?? 'N/A',
                    'email' => $this->creator->email ?? 'N/A',
                ];
            }),
        ];
    }
}
