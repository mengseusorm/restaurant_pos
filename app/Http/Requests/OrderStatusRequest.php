<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class OrderStatusRequest extends FormRequest
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
            'branch_id'    => ['nullable', 'integer'],
            'status_code' => [
                'required',
                'integer',
                Rule::unique("order_statuses", "status_code")->ignore($this->route('orderStatus'))
            ],
            'name'        => ['required', 'string', 'max:190'],
            'name_kh'     => ['nullable', 'string', 'max:190'],
            'name_cn'     => ['nullable', 'string', 'max:190'],
            'name_en'     => ['nullable', 'string', 'max:190'],
            'status_order'=> ['nullable', 'integer'],
            'status'      => ['required', 'integer', 'in:5,10'],
        ];
    }
}
