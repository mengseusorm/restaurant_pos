<?php

namespace Database\Seeders;

use App\Enums\Status;
use App\Models\PaymentMethod;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PaymentMethodSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        PaymentMethod::create([
            'id'              => 1,
            'user_id'         => null,
            'name'            => 'Cash',
            'value'           => 1,
            'provider'        => null,
            'account_name'    => null,
            'account_number'  => null,
            'expiry_date'     => null,
            'billing_address' => null,
            'is_default'      => 1,
            'order_number'    => 1,
            'status'          => Status::ACTIVE
        ]);
    }
}
