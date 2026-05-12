<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PrintLabelSetting extends Model
{
    use HasFactory;

    protected $table = "print_label_settings";
    protected $fillable = [
        'name',
        'show_company_name',
        'show_branch_name',
        'show_phone_number',
        'show_order_number',
        'show_order_number_barcode',
        'show_order_qr_code',
        'show_item',
        'show_item_qty',
        'show_item_price',
        'show_customer_name',
        'show_customer_phone_number',
        'show_delivery_address',
        'show_payment_status',
        'show_payment_qr_code',
        'show_payment_method',
        'print_qty',
        'label_title',
        'label_width',
        'label_height',
        'separate_item',
        'separate_qty',
        'label_style_custom',
    ];

    protected $casts = [
        'show_company_name'           => 'integer',
        'show_branch_name'            => 'integer',
        'show_phone_number'           => 'integer',
        'show_order_number'           => 'integer',
        'show_order_number_barcode'   => 'integer',
        'show_order_qr_code'          => 'integer',
        'show_item'                   => 'integer',
        'show_item_qty'               => 'integer',
        'show_item_price'             => 'integer',
        'show_customer_name'          => 'integer',
        'show_customer_phone_number'  => 'integer',
        'show_delivery_address'       => 'integer',
        'show_payment_status'         => 'integer',
        'show_payment_qr_code'        => 'integer',
        'show_payment_method'         => 'integer',
        'print_qty'                   => 'integer',     
        'label_title'                 => 'integer',
        'label_width'                 => 'integer',
        'label_height'                => 'integer',
        'separate_item'               => 'integer',
        'separate_qty'                => 'integer',
    ];

}
