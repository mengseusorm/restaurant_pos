<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ExchangeRateRequest extends FormRequest
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
        $rules = [
            'base_currency' => [
                'required',
                'string',
                'max:3',
                'exists:currencies,code',
            ],
            'target_currency' => [
                'required',
                'string',
                'max:3',
                'exists:currencies,code',
                'different:base_currency', // Cannot be the same as base currency
            ],
            'rate' => [
                'required',
                'numeric',
                'min:0',
                'max:9999999999.9999999999',
                'regex:/^\d{0,10}(\.\d{1,10})?$/',
            ],
            'effective_at' => [
                'nullable',
                'date',
            ],
            'source' => [
                'nullable',
                'string',
                'max:50',
                'in:manual,api,bank,system',
            ],
        ];

        // Add unique validation for create (POST) requests
        if ($this->isMethod('post')) {
            $rules['base_currency'][] = Rule::unique('exchange_rates')
                ->where('target_currency', $this->target_currency);
        }

        return $rules;
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'target_currency.different' => 'The target currency must be different from the base currency.',
            'base_currency.exists' => 'The selected base currency does not exist.',
            'target_currency.exists' => 'The selected target currency does not exist.',
        ];
    }
}
