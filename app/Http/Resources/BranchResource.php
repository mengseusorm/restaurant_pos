<?php

namespace App\Http\Resources;


use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Log;

class BranchResource extends JsonResource
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
            "name_en"                      => $this->name_en,
            "code"                         => $this->code,
            "email"                        => $this->email === null ? '' : $this->email,
            "phone"                        => $this->phone === null ? '' : $this->phone,
            "latitude"                     => $this->latitude === null ? '' : $this->latitude,
            "longitude"                    => $this->longitude === null ? '' : $this->longitude,
            "city"                         => $this->city,
            "state"                        => $this->state,
            "zip_code"                     => $this->zip_code,
            "address"                      => $this->address,
            "status"                       => $this->status,
            'currency_id'                  => $this->currency,
            'language_id'                  => $this->language,
            'close_business_day_time'      => $this->close_business_day_time,
            'current_business_day'         => $this->current_business_day,
            'open_time'                    => $this->open_time,
            'close_time'                   => $this->close_time,
            'show_unpaid_button'           => $this->show_unpaid_button,
            'change_status_paid_to_unpaid' => $this->change_status_paid_to_unpaid,
            'show_delete_order_button'     => $this->show_delete_order_button,
            'show_select_table'            => $this->show_select_table,
            'show_select_table_list'       => $this->show_select_table_list,
            'show_token'                   => $this->show_token,
            'show_delivery'                => $this->show_delivery,
            'show_waiting_number'          => $this->show_waiting_number,
            'show_suspense_button'         => $this->show_suspense_button,
            'show_paid_order_button'       => $this->show_paid_order_button,
            'show_sidebar_table_list'      => $this->show_sidebar_table_list,
            'unpaid_order_show_invoice'    => $this->unpaid_order_show_invoice,
            'show_receive_amount'          => $this->show_receive_amount,
            'show_select_customer'         => $this->show_select_customer,
            'show_input_number_of_people'  => $this->show_input_number_of_people,
            'default_selected_order_type'  => $this->default_selected_order_type,
            'show_select_member'           => $this->show_select_member,
            'member_can_redeem_point'      => $this->member_can_redeem_point,
            'show_online_order_button'     => $this->show_online_order_button,
            'show_pending_order_button'    => $this->show_pending_order_button,
            'show_pos_button'              => $this->show_pos_button,
            'show_retail_pos_button'       => $this->show_retail_pos_button,
            'show_quick_pos_button'        => $this->show_quick_pos_button,
            'show_floor_plan'              => $this->show_floor_plan,
            'show_table_button'            => $this->show_table_button,
            'show_customer_view_button'    => $this->show_customer_view_button,
            'show_navbar_button_text'      => $this->show_navbar_button_text,
            'show_customer_name'           => $this->show_customer_name,
            'show_customer_phone_number'   => $this->show_customer_phone_number,
            'show_customer_address'        => $this->show_customer_address,
            "online_order_slug"            => $this->online_order_slug,
            "telegram_mini_app_slug"       => $this->telegram_mini_app_slug,
            "shop_category"                => $this->shopCategory,
            "show_btn_print_web"           => $this->show_btn_print_web,
            "show_btn_print"               => $this->show_btn_print,
            "show_print_label_button"      => $this->show_print_label_button,
            "show_discount_button"         => $this->show_discount_button,
            "create_paid_order_confirm"    => $this->create_paid_order_confirm,
            "create_unpaid_order_confirm"  => $this->create_unpaid_order_confirm,
            "create_paid_order_auto_print" => $this->create_paid_order_auto_print,
            "create_unpaid_order_auto_print" => $this->create_unpaid_order_auto_print,
            "void_order_auto_print"        => $this->void_order_auto_print,
            "change_item_qty_auto_print"   => $this->change_item_qty_auto_print,
            "unpaid_print_bill"            => $this->unpaid_print_bill,
            "unpaid_print_invoice"         => $this->unpaid_print_invoice,
            "open_table_confirm"           => $this->open_table_confirm,
            "payment_auto_release_table"   => $this->payment_auto_release_table,
            "open_time"                    => $this->open_time,
            "close_time"                   => $this->close_time,
        ];
    }
}
