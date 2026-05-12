<?php

namespace Database\Seeders;

use App\Enums\OrderType as OrderTypeEnum;
use App\Models\OrderType;
use Illuminate\Database\Seeder;

class OrderTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $orderTypes = [
            [
                'branch_id' => 0,
                'type_code' => OrderTypeEnum::DELIVERY,
                'name' => 'Delivery',
                'name_kh' => 'ការដឹកជញ្ជូន',
                'name_cn' => '送货',
                'type_order' => 1,
            ],
            [
                'branch_id' => 0,
                'type_code' => OrderTypeEnum::TAKEAWAY,
                'name' => 'Takeaway',
                'name_kh' => 'យកទៅ',
                'name_cn' => '外卖',
                'type_order' => 2,
            ],
            [
                'branch_id' => 0,
                'type_code' => OrderTypeEnum::POS,
                'name' => 'POS',
                'name_kh' => 'POS',
                'name_cn' => 'POS',
                'type_order' => 3,
            ],
            [
                'branch_id' => 0,
                'type_code' => OrderTypeEnum::DINING_TABLE,
                'name' => 'Dining Table',
                'name_kh' => 'តុញ៉ាំ',
                'name_cn' => '餐桌',
                'type_order' => 4,
            ],
            [
                'branch_id' => 0,
                'type_code' => OrderTypeEnum::TOKEN,
                'name' => 'Token',
                'name_kh' => 'ថូខឹន',
                'name_cn' => '令牌',
                'type_order' => 5,
            ],
            [
                'branch_id' => 0,
                'type_code' => OrderTypeEnum::ONLINE_ORDER,
                'name' => 'Online Order',
                'name_kh' => 'ការបញ្ជាទិញតាមអ៊ីនធឺណិត',
                'name_cn' => '在线订单',
                'type_order' => 6,
            ],
        ];

        foreach ($orderTypes as $orderType) {
            OrderType::create($orderType);
        }
    }
}
