<?php

namespace App\Http\PaymentGateways\Requests;

use App\Enums\Activity;
use Illuminate\Foundation\Http\FormRequest;

class Abapayway extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        if (request()->abapayway_status == Activity::ENABLE) {
            return [
                'usd_account'          => ['nullable', 'string'],
                'khr_account'          => ['nullable', 'string'],
                'merchant_id'          => ['required', 'string'],
                'api_key'              => ['required', 'string'],
                'mode'                 => ['required', 'string'],
                'abapayway_status'     => ['nullable', 'numeric'],
                'callback_url'         => ['nullable', 'string'],
                'continue_success_url' => ['nullable', 'string'],
                'return_url'           => ['nullable', 'string'],
            ];
        } else {
            return [
                'merchant_id'          => ['nullable', 'string'],
                'api_key'              => ['nullable', 'string'],
                'mode'                 => ['nullable', 'string'],
                'callback_url'         => ['nullable', 'string'],
                'continue_success_url' => ['nullable', 'string'],
                'return_url'           => ['nullable', 'string'],
                'abapayway_status'     => ['nullable', 'numeric'],
            ];
        }
    }
}