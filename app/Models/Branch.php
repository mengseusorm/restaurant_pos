<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    use HasFactory;
    protected $table = "branches";
    protected $fillable = [
        'name',
        'name_kh',
        'name_cn',
        'name_en',
        'code',
        'online_order_slug',
        'telegram_mini_app_slug',
        'email',
        'phone',
        'latitude',
        'longitude',
        'city',
        'state',
        'zip_code',
        'address',
        'status',
        'currency_id',
        'language_id',
        'close_business_day_time',
        'current_business_day',
        'open_time',
        'close_time',

        // POS Settings
        'show_unpaid_button',
        'unpaid_order_show_invoice',
        'change_status_paid_to_unpaid',
        'show_delete_order_button',
        'show_select_table',
        'show_select_table_list',
        'show_token',
        'show_delivery',
        'show_waiting_number',
        'show_suspense_button',
        'show_paid_order_button',
        'show_sidebar_table_list',
        'show_receive_amount',
        'show_select_customer',
        'show_input_number_of_people',
        'default_selected_order_type',
        'show_select_member',
        'member_can_redeem_point',
        'show_online_order_button',
        'show_pending_order_button',
        'show_pos_button',
        'show_retail_pos_button',
        'show_quick_pos_button',
        'show_floor_plan',
        'show_table_button',
        'show_customer_view_button',
        'show_navbar_button_text',
        'show_customer_name',
        'show_customer_phone_number',
        'show_customer_address',
        'shop_category_id',
        'show_btn_print_web',
        'show_btn_print',
        'show_print_label_button',
        'show_discount_button',
        'create_paid_order_confirm',
        'create_unpaid_order_confirm',
        'create_paid_order_auto_print',
        'create_unpaid_order_auto_print',
        'void_order_auto_print',
        'change_item_qty_auto_print',
        'unpaid_print_bill',
        'unpaid_print_invoice',
        'open_table_confirm',
        'payment_auto_release_table',
        'open_time',
        'close_time'
    ];
    protected $casts = [
        'id'                           => 'integer',
        'name'                         => 'string',
        'name_kh'                      => 'string',
        'name_cn'                      => 'string',
        'name_en'                      => 'string',
        'code'                         => 'string',
        'online_order_slug'            => 'string',
        'telegram_mini_app_slug'       => 'string',
        'email'                        => 'string',
        'phone'                        => 'string',
        'latitude'                     => 'string',
        'longitude'                    => 'string',
        'city'                         => 'string',
        'state'                        => 'string',
        'zip_code'                     => 'string',
        'address'                      => 'string',
        'status'                       => 'integer',
        'currency_id'                  => 'integer',
        'language_id'                  => 'integer',
        'close_business_day_time'      => 'datetime',
        'current_business_day'         => 'datetime',
        'open_time'                    => 'string',
        'close_time'                   => 'string',
        'show_unpaid_button'           => 'integer',
        'change_status_paid_to_unpaid' => 'integer',
        'show_delete_order_button'     => 'integer',
        'show_select_table'            => 'integer',
        'show_select_table_list'       => 'integer',
        'show_token'                   => 'integer',
        'show_delivery'                => 'integer',
        'show_waiting_number'          => 'integer',
        'show_suspense_button'         => 'integer',
        'show_paid_order_button'       => 'integer',
        'show_sidebar_table_list'      => 'integer',
        'unpaid_order_show_invoice'    => 'integer',
        'show_receive_amount'          => 'integer',
        'show_select_customer'         => 'integer',
        'show_input_number_of_people'  => 'integer',
        'default_selected_order_type'  => 'integer',
        'show_select_member'           => 'integer',
        'member_can_redeem_point'      => 'integer',
        'show_online_order_button'     => 'integer',
        'show_pending_order_button'    => 'integer',
        'show_pos_button'              => 'integer',
        'show_retail_pos_button'       => 'integer',
        'show_quick_pos_button'        => 'integer',
        'show_floor_plan'              => 'integer',
        'show_table_button'            => 'integer',
        'show_customer_view_button'    => 'integer',
        'show_navbar_button_text'      => 'integer',
        'show_customer_name'           => 'integer',
        'show_customer_phone_number'   => 'integer',
        'show_customer_address'        => 'integer',
        'shop_category_id'             => 'integer',
        'show_btn_print_web'           => 'integer',
        'show_btn_print'               => 'integer',
        'show_print_label_button'      => 'integer',
        'show_discount_button'         => 'integer',
        'create_paid_order_confirm'    => 'integer',
        'create_unpaid_order_confirm'  => 'integer',
        'create_paid_order_auto_print' => 'integer',
        'create_unpaid_order_auto_print' => 'integer',
        'void_order_auto_print'        => 'integer',
        'change_item_qty_auto_print'   => 'integer',
        'unpaid_print_bill'            => 'integer',
        'unpaid_print_invoice'         => 'integer',
        'open_table_confirm'           => 'integer',
        'payment_auto_release_table'   => 'integer',
        'open_time'                    => 'string',
        'close_time'                   => 'string',
    ];

    public function itemStock()
    {
        return $this->hasMany(ItemStock::class);
    }

    public function item() : \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Item::class);
    }

    public function kitchenPrinter() : \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(kitchenPrinter::class);
    }

    public function itemCategories() : \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ItemCategory::class);
    }
    /**
     * add language
     */
    public function language() : \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Language::class);
    }
    /**
     * add currency
     */
    public function currency() : \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    public function orderItem() : \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function members(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Member::class);
    }

    public function memberPointTransactions(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(MemberPointTransaction::class);
    }

    public function pointEarnRules(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(PointEarnRule::class);
    }

    public function pointUsageRules(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(PointUsageRule::class);
    }

    public function pointGifts(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(PointGift::class);
    }

    public function shopCategory(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(ShopCategory::class);
    }
}
