<?php

namespace App\Http\Resources;


use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Log;

class BranchMinimalResource extends JsonResource
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
            "id"                           => $this->id,
            "name"                         => $this->name,
            // "email"                        => $this->email === null ? '' : $this->email,
            // "phone"                        => $this->phone === null ? '' : $this->phone,
            // "latitude"                     => $this->latitude === null ? '' : $this->latitude,
            // "longitude"                    => $this->longitude === null ? '' : $this->longitude,
            // "city"                         => $this->city,
            // "state"                        => $this->state,
            // "zip_code"                     => $this->zip_code,
            // "address"                      => $this->address,
            // "status"                       => $this->status,
            // 'currency_id'                  => $this->currency,
            // 'language_id'                  => $this->language,
            'currency'                     => $this->currency,
            'currency_id'                  => $this->currency->id ?? null,
            'currency_code'                => $this->currency->code ?? null,
            'language_id'                  => $this->language->id ?? null,
            'language_name'                => $this->language->name ?? null,

            // 'close_business_day_time'      => $this->close_business_day_time,
            // 'current_business_day'         => $this->current_business_day,
            // 'show_unpaid_button'           => $this->show_unpaid_button,
            // 'change_status_paid_to_unpaid' => $this->change_status_paid_to_unpaid,
            // 'show_delete_order_button'     => $this->show_delete_order_button,
            // 'show_select_table'            => $this->show_select_table,
            // 'show_select_table_list'       => $this->show_select_table_list,
            // 'show_token'                   => $this->show_token,
            // 'show_delivery'                => $this->show_delivery,
            // 'show_waiting_number'          => $this->show_waiting_number,
            // 'show_suspense_button'         => $this->show_suspense_button,
            // 'show_paid_order_button'       => $this->show_paid_order_button,
            // 'show_sidebar_table_list'      => $this->show_sidebar_table_list,
            // 'unpaid_order_show_invoice'    => $this->unpaid_order_show_invoice,
            // 'show_receive_amount'          => $this->show_receive_amount,
            // 'show_select_customer'         => $this->show_select_customer,
            // 'show_input_number_of_people'  => $this->show_input_number_of_people,
        ];
    }
}
