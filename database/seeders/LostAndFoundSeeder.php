<?php

namespace Database\Seeders;

use App\Enums\LostAndFoundStatus;
use App\Models\Branch;
use App\Models\LostAndFound;
use App\Models\User;
use Illuminate\Database\Seeder;

class LostAndFoundSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $branch = Branch::first();
        $users = User::limit(3)->get();

        if ($branch && $users->count() > 0) {
            $items = [
                [
                    'item_code' => 'LF-' . date('Ymd') . '-001',
                    'item_name' => 'Black Leather Wallet',
                    'found_date' => now()->subDays(3),
                    'found_by' => $users[0]->id,
                    'found_location' => 'Table 5',
                    'customer_name' => 'John Smith',
                    'customer_phone' => '+1234567890',
                    'customer_email' => 'john.smith@example.com',
                    'status' => LostAndFoundStatus::CLAIMED,
                    'claimed_by' => 'John Smith',
                    'claimed_date' => now()->subDays(2),
                    'notes' => 'Customer verified ownership with ID card',
                    'branch_id' => $branch->id,
                    'created_by' => $users[0]->id,
                    'storage_location' => 'Front Desk - Drawer 2'
                ],
                [
                    'item_code' => 'LF-' . date('Ymd') . '-002',
                    'item_name' => 'iPhone 13 Pro',
                    'found_date' => now()->subDays(2),
                    'found_by' => $users[1]->id,
                    'found_location' => 'Restroom',
                    'status' => LostAndFoundStatus::FOUND,
                    'notes' => 'Phone is locked, waiting for owner to claim',
                    'branch_id' => $branch->id,
                    'created_by' => $users[1]->id,
                    'storage_location' => 'Manager Office - Safe'
                ],
                [
                    'item_code' => 'LF-' . date('Ymd') . '-003',
                    'item_name' => 'Blue Umbrella',
                    'found_date' => now()->subDays(5),
                    'found_by' => $users[0]->id,
                    'found_location' => 'Table 12',
                    'status' => LostAndFoundStatus::FOUND,
                    'notes' => 'Standard folding umbrella',
                    'branch_id' => $branch->id,
                    'created_by' => $users[0]->id,
                    'storage_location' => 'Lost & Found Rack'
                ],
                [
                    'item_code' => 'LF-' . date('Ymd') . '-004',
                    'item_name' => 'Car Keys (Toyota)',
                    'found_date' => now()->subDays(1),
                    'found_by' => $users[2]->id,
                    'found_location' => 'Parking Area',
                    'customer_name' => 'Sarah Johnson',
                    'customer_phone' => '+1987654321',
                    'status' => LostAndFoundStatus::CLAIMED,
                    'claimed_by' => 'Sarah Johnson',
                    'claimed_date' => now(),
                    'notes' => 'Customer very grateful, left positive feedback',
                    'branch_id' => $branch->id,
                    'created_by' => $users[2]->id,
                    'storage_location' => 'Front Desk'
                ],
                [
                    'item_code' => 'LF-' . date('Ymd') . '-005',
                    'item_name' => 'Reading Glasses',
                    'found_date' => now()->subDays(30),
                    'found_by' => $users[1]->id,
                    'found_location' => 'Table 8',
                    'status' => LostAndFoundStatus::DISPOSED,
                    'disposal_date' => now()->subDays(1),
                    'notes' => 'Unclaimed after 30 days, donated to charity',
                    'branch_id' => $branch->id,
                    'created_by' => $users[1]->id,
                    'storage_location' => 'N/A'
                ]
            ];

            foreach ($items as $item) {
                LostAndFound::create($item);
            }
        }
    }
}
