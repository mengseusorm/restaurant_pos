<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CurrencyRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize() : bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules() : array
    {
        return [
            'name'              => [
                'required',
                'string',
                'max:190',
                Rule::unique("currencies", "name")->ignore($this->route('currency.id'))
            ],
            'name_kh'           => ['nullable', 'string', 'max:190'],
            'name_cn'           => ['nullable', 'string', 'max:190'],
            'symbol'            => ['required', 'string', 'max:190'],
            'code'              => ['required', 'string', 'max:20'],
            'decimal_places'    => ['nullable', 'integer', 'min:-2', 'max:10'],
            'is_cryptocurrency' => ['required', 'numeric', 'max:15'],
            'exchange_rate'     => ['nullable', 'numeric', 'min:0', 'max:9999999999.9999999999', 'regex:/^\d{0,10}(\.\d{1,10})?$/'],
            'second_currency'   => ['nullable', 'string', 'max:20'],
            'second_currency_exchange_rate' => ['nullable'],
            'second_decimal'    => ['nullable', 'integer', 'max:20'],
            'show_exchange_rate_on_invoice' => ['nullable', 'numeric'],
        ];
    }
}
