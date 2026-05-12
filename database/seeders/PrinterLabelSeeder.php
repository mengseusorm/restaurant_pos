<?php

namespace Database\Seeders;

use App\Enums\Status;
use App\Models\PrintLabelSetting;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PrinterLabelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        PrintLabelSetting::create([
            'id'                          => 1,
            'name'                        => 'Default Label Setting',
            'show_company_name'           => Status::ACTIVE,
            'show_branch_name'            => Status::ACTIVE,
            'show_phone_number'           => Status::ACTIVE,
            'show_order_number'           => Status::ACTIVE,
            'show_order_number_barcode'   => Status::ACTIVE,
            'show_order_qr_code'          => Status::ACTIVE,
            'show_item'                   => Status::ACTIVE,
            'show_item_qty'               => Status::ACTIVE,
            'show_item_price'             => Status::ACTIVE,
            'show_customer_name'          => Status::ACTIVE,
            'show_customer_phone_number'  => Status::ACTIVE,
            'show_delivery_address'       => Status::ACTIVE,
            'show_payment_status'         => Status::ACTIVE,
            'show_payment_qr_code'        => Status::ACTIVE,
            'show_payment_method'         => Status::ACTIVE,
            'print_qty'                   => Status::ACTIVE,
            'label_title'                 => Status::ACTIVE,
            'label_width'                 => 50,
            'label_height'                => 30,
            'separate_item'               => Status::INACTIVE,
            'separate_qty'                => Status::INACTIVE,
        ]);
    }
}
