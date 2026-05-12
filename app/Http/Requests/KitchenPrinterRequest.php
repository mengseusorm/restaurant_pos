<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class KitchenPrinterRequest extends FormRequest
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
            'name'           => ['required', 'string', 'max:50'],
            'ip'             => ['nullable','string'],
            'port'           => ['nullable','string'],
            'printer_type'   => ['nullable'],
            'label'          => ['string'],   
            'printer_method' => ['integer'],
            'branch_id'      => ['required','integer'], 
            'printer_server' => ['string','nullable'], 
            'print_copies'   => ['integer']
        ];
    }
}
