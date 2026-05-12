<?php

namespace Database\Seeders;

use App\Enums\OrderStatus as OrderStatusEnum;
use App\Models\OrderStatus;
use Illuminate\Database\Seeder;

class OrderStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $orderStatuses = [
            [
                'branch_id' => 0,
                'status_code' => OrderStatusEnum::PENDING,
                'name' => 'Pending',
                'name_kh' => 'កំពុងរង់ចាំ',
                'name_cn' => '待处理',
                'status_order' => 1,
            ],
            [
                'branch_id' => 0,
                'status_code' => OrderStatusEnum::ACCEPT,
                'name' => 'Accept',
                'name_kh' => 'ទទួលយក',
                'name_cn' => '接受',
                'status_order' => 2,
            ],
            [
                'branch_id' => 0,
                'status_code' => OrderStatusEnum::PROCESSING,
                'name' => 'Processing',
                'name_kh' => 'កំពុងដំណើរការ',
                'name_cn' => '处理中',
                'status_order' => 3,
            ],
            [
                'branch_id' => 0,
                'status_code' => OrderStatusEnum::OUT_FOR_DELIVERY,
                'name' => 'Out For Delivery',
                'name_kh' => 'កំពុងដឹកជញ្ជូន',
                'name_cn' => '配送中',
                'status_order' => 4,
            ],
            [
                'branch_id' => 0,
                'status_code' => OrderStatusEnum::DELIVERED,
                'name' => 'Delivered',
                'name_kh' => 'បានដឹកជញ្ជូន',
                'name_cn' => '已送达',
                'status_order' => 5,
            ],
            [
                'branch_id' => 0,
                'status_code' => OrderStatusEnum::CANCELED,
                'name' => 'Canceled',
                'name_kh' => 'បានបោះបង់',
                'name_cn' => '已取消',
                'status_order' => 6,
            ],
            [
                'branch_id' => 0,
                'status_code' => OrderStatusEnum::REJECTED,
                'name' => 'Rejected',
                'name_kh' => 'បានបដិសេធ',
                'name_cn' => '已拒绝',
                'status_order' => 7,
            ],
            [
                'branch_id' => 0,
                'status_code' => OrderStatusEnum::RETURNED,
                'name' => 'Returned',
                'name_kh' => 'បានត្រឡប់មកវិញ',
                'name_cn' => '已退回',
                'status_order' => 8,
            ],
        ];

        foreach ($orderStatuses as $status) {
            OrderStatus::create($status);
        }
    }
}
