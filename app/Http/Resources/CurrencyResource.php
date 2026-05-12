<?php

namespace App\Http\Resources;


use App\Libraries\AppLibrary;
use Illuminate\Http\Resources\Json\JsonResource;

class CurrencyResource extends JsonResource
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
            "id"                            => $this->id,
            "name"                          => $this->name,
            "name_kh"                       => $this->name_kh,
            "name_cn"                       => $this->name_cn,
            "name_symbol"                   => $this->name . ' (' . $this->symbol . ')',
            "symbol"                        => $this->symbol,
            "code"                          => $this->code,
            "decimal_places"                => $this->decimal_places,
            "is_cryptocurrency"             => $this->is_cryptocurrency,
            // "exchange_rate"                 => AppLibrary::convertAmountFormat($this->exchange_rate),
            "exchange_rate"                 => $this->exchange_rate,
            "second_currency"               => $this->second_currency,
            "second_currency_exchange_rate" => $this->second_currency_exchange_rate,
            "second_decimal"                => $this->second_decimal,
            "show_exchange_rate_on_invoice" => $this->show_exchange_rate_on_invoice,
        ];
    }
}
