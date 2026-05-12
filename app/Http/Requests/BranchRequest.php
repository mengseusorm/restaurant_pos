<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BranchRequest extends FormRequest
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

        return [
            'name'      => [
                'required',
                'string',
                'max:190',
                Rule::unique("branches", "name")->ignore($this->route('branch.id'))
            ],
            'name_kh'                      => ['nullable', 'string', 'max:190'],
            'name_cn'                      => ['nullable', 'string', 'max:190'],
            'name_en'                      => ['nullable', 'string', 'max:190'],
            'code'                         => ['nullable', 'string', 'max:50'],
            'email'                        => ['nullable', 'email', 'max:190'],
            'phone'                        => ['nullable', 'string', 'max:20'],
            'latitude'                     => ['nullable', 'max:190'],
            'longitude'                    => ['nullable', 'max:190'],
            'city'                         => ['required', 'string', 'max:190'],
            'state'                        => ['required', 'string', 'max:190'],
            'zip_code'                     => ['required', 'string'],
            'address'                      => ['required', 'string', 'max:500'],
            'status'                       => ['required', 'numeric', 'max:24'],
            'currency_id'                  => ['nullable', 'integer'],
            'language_id'                  => ['required', 'integer'],
            // 'close_business_day_time' => ['required', 'date_format:H:i:s'],
            'close_business_day_time'      => ['nullable'],
            'current_business_day'         => ['nullable'],
            'open_time'                    => ['nullable', 'date_format:H:i'],
            'close_time'                   => ['nullable', 'date_format:H:i'],
            'show_unpaid_button'           => ['required', 'numeric'],
            'change_status_paid_to_unpaid' => ['required', 'numeric'],
            'show_delete_order_button'     => ['required', 'numeric'],
            //add option request
            'show_select_table'            => ['required', 'numeric'],
            'show_select_table_list'       => ['required', 'numeric'],
            'show_token'                   => ['required', 'numeric'],
            'show_delivery'                => ['required', 'numeric'],
            'show_waiting_number'          => ['required', 'numeric'],
            'show_suspense_button'         => ['required', 'numeric'],
            'show_paid_order_button'       => ['required', 'numeric'],
            'show_sidebar_table_list'      => ['required', 'numeric'],
            'unpaid_order_show_invoice'    => ['nullable', 'numeric'],
            'show_receive_amount'          => ['nullable', 'numeric'],
            'show_select_customer'         => ['nullable', 'numeric'],
            'show_input_number_of_people'  => ['nullable', 'numeric'],
            'default_selected_order_type'  => ['required', 'numeric'],
            'show_select_member'           => ['nullable', 'numeric'],
            'member_can_redeem_point'      => ['nullable', 'numeric'],
            'show_online_order_button'     => ['nullable', 'numeric'],
            'show_pending_order_button'    => ['nullable', 'numeric'],
            'show_pos_button'              => ['nullable', 'numeric'],
            'show_retail_pos_button'       => ['nullable', 'numeric'],
            'show_quick_pos_button'        => ['nullable', 'numeric'],
            'show_floor_plan'              => ['nullable', 'numeric'],
            'show_table_button'            => ['nullable', 'numeric'],
            'show_customer_view_button'    => ['nullable', 'numeric'],
            'show_navbar_button_text'      => ['nullable', 'numeric'],
            'show_customer_name'           => ['nullable', 'numeric'],
            'show_customer_phone_number'   => ['nullable', 'numeric'],
            'show_customer_address'        => ['nullable', 'numeric'],
            'online_order_slug'            => [
                'nullable',
                'string',
                'max:190',
                Rule::unique("branches", "online_order_slug")->ignore($this->route('branch.id'))
            ],
            'telegram_mini_app_slug'       => [
                'nullable',
                'string',
                'max:190',
                Rule::unique("branches", "telegram_mini_app_slug")->ignore($this->route('branch.id'))
            ],
            "shop_category_id"              => ['nullable', 'integer'],
            "show_btn_print_web"            => ['nullable', 'numeric'],
            "show_btn_print"                => ['nullable', 'numeric'],
            "show_print_label_button"       => ['nullable', 'numeric'],
            "show_discount_button"          => ['nullable', 'numeric'],
            "create_paid_order_confirm"     => ['nullable', 'numeric'],
            "create_unpaid_order_confirm"   => ['nullable', 'numeric'],
            "create_paid_order_auto_print"  => ['nullable', 'numeric'],
            "create_unpaid_order_auto_print" => ['nullable', 'numeric'],
            "void_order_auto_print"         => ['nullable', 'numeric'],
            "change_item_qty_auto_print"    => ['nullable', 'numeric'],
            "unpaid_print_bill"             => ['nullable', 'numeric'],
            "unpaid_print_invoice"          => ['nullable', 'numeric'],
            "open_table_confirm"            => ['nullable', 'numeric'],
            "payment_auto_release_table"    => ['nullable', 'numeric'],
            'open_time'                     => ['nullable', 'regex:/^([0-1]?[0-9]|2[0-3]):[0-5][0-9](:[0-5][0-9])?$/'],
            'close_time'                    => ['nullable', 'regex:/^([0-1]?[0-9]|2[0-3]):[0-5][0-9](:[0-5][0-9])?$/'],
        ];
    }
}
