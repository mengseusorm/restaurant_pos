<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StockRecordRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'item_id'   => ['required','integer'],
            'stock_id'  => ['required','integer'],
            'user_id'           => ['required'],
            'quantity'          => ['required','numeric','min:0'],
            'record_type'       => ['nullable','string'],
            'to_warehouse_id'   => 'nullable',
            'from_warehouse_id' => 'nullable',
        ];
    }
}
