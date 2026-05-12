<?php

namespace App\Http\PaymentGateways\Requests;

use App\Enums\Activity;
use Illuminate\Foundation\Http\FormRequest;

class Huione extends FormRequest
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
        if (request()->huione_status == Activity::ENABLE) {
            return [
                'huione_app_id'     => ['required', 'string'],
                'huione_secret_key' => ['required', 'string'],
                'huione_mode'       => ['required', 'string'],
                'huione_status'     => ['nullable', 'numeric'],
            ];
        } else {
            return [
                'huione_app_id'     => ['nullable', 'string'],
                'huione_secret_key' => ['nullable', 'string'],
                'huione_mode'       => ['nullable', 'string'],
                'huione_status'     => ['nullable', 'numeric'],
            ];
        }
    }
}