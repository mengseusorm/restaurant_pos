<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReservationRequest extends FormRequest
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
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'required|string|max:20',
            'customer_email' => 'nullable|email|max:255',
            'reservation_date' => 'required|date',
            'reservation_time' => 'required',
            'number_of_people' => 'required|integer|min:1',
            'table_id' => 'nullable|exists:dining_tables,id',
            'status' => 'required|integer',
            'special_request' => 'nullable|string',
            'branch_id' => 'nullable|exists:branches,id',
            'deposit_amount' => 'nullable|numeric|min:0',
            'payment_status' => 'nullable|integer',
            'check_in_time' => 'nullable|date',
            'check_out_time' => 'nullable|date',
            'cancel_reason' => 'nullable|string',
            'reminder_sent' => 'nullable|boolean',
            'duration_minutes' => 'nullable|integer|min:0',
        ];
    }
}
