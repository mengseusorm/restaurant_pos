<?php

namespace App\Http\Resources;


use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Log;

class BranchPublicResource extends JsonResource
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
            "name_kh"                      => $this->name_kh,
            "name_cn"                      => $this->name_cn,
            "code"                         => $this->code,
            // "email"                        => $this->email === null ? '' : $this->email,
            // "phone"                        => $this->phone === null ? '' : $this->phone,
            // "latitude"                     => $this->latitude === null ? '' : $this->latitude,
            // "longitude"                    => $this->longitude === null ? '' : $this->longitude,
            // "city"                         => $this->city,
            // "state"                        => $this->state,
            // "zip_code"                     => $this->zip_code,
            "address"                      => $this->address,
            // "status"                       => $this->status,
            // 'currency_id'                  => $this->currency,
            'currency_symbol'              => optional($this->currency)->symbol ?? '',
            'currency_code'                => optional($this->currency)->code ?? '',
            // 'language_id'                  => $this->language,
            'language_name'                => optional($this->language)->name ?? '',
            'language_code'                => optional($this->language)->code ?? '',
            'branch_id'                     => $this->id,
            'branch_name'                   => $this->name,
            'online_order_slug'            => $this->online_order_slug,
            // 'close_business_day_time'      => $this->close_business_day_time,
            // 'current_business_day'         => $this->current_business_day,
            // 'show_unpaid_button'           => $this->show_unpaid_button,
            // 'change_status_paid_to_unpaid' => $this->change_status_paid_to_unpaid,
            // 'show_delete_order_button'     => $this->show_delete_order_button,
            // 'show_select_table'            => $this->show_select_table,
            // 'show_token'                   => $this->show_token,
            // 'show_delivery'                => $this->show_delivery,
            // 'show_waiting_number'          => $this->show_waiting_number,
            // 'show_suspense_button'         => $this->show_suspense_button,
            // 'show_paid_order_button'       => $this->show_paid_order_button,
            // 'show_sidebar_table_list'      => $this->show_sidebar_table_list,
            'show_customer_name'            => $this->show_customer_name,
            'show_customer_phone_number'    => $this->show_customer_phone_number,
            'show_customer_address'         => $this->show_customer_address,
            'open_time'                     => $this->open_time,
            'close_time'                    => $this->close_time,
            'currency_id'                  => $this->currency,
        ];
    }
}
