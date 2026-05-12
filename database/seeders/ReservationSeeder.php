<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Reservation;
use App\Models\DiningTable;
use App\Models\Branch;
use App\Models\User;
use App\Enums\ReservationStatus;
use App\Enums\PaymentStatus;
use Illuminate\Support\Facades\DB;

class ReservationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Get first available user (staff)
        $user = User::first();
        if (!$user) {
            $this->command->error('No users found. Please create users first.');
            return;
        }

        // Get first available branch (optional)
        $branch = Branch::first();
        
        // Get available tables
        $tables = DiningTable::limit(5)->get();
        if ($tables->isEmpty()) {
            $this->command->warn('No dining tables found. Reservations will be created without table assignments.');
        }

        $reservations = [
            [
                'customer_name' => 'John Smith',
                'customer_phone' => '+1234567890',
                'customer_email' => 'john.smith@example.com',
                'reservation_date' => now()->addDays(1),
                'reservation_time' => '18:00:00',
                'number_of_people' => 4,
                'table_id' => $tables->isNotEmpty() ? $tables[0]->id : null,
                'status' => ReservationStatus::CONFIRMED,
                'special_request' => 'Window seat preferred, celebrating anniversary',
                'deposit_amount' => 50.00,
                'payment_status' => PaymentStatus::PARTIAL,
                'duration_minutes' => 120,
            ],
            [
                'customer_name' => 'Emily Johnson',
                'customer_phone' => '+1234567891',
                'customer_email' => 'emily.j@example.com',
                'reservation_date' => now()->addDays(2),
                'reservation_time' => '19:30:00',
                'number_of_people' => 2,
                'table_id' => $tables->count() > 1 ? $tables[1]->id : null,
                'status' => ReservationStatus::PENDING,
                'special_request' => 'Vegetarian menu please',
                'deposit_amount' => 0.00,
                'payment_status' => PaymentStatus::UNPAID,
                'duration_minutes' => 90,
            ],
            [
                'customer_name' => 'Michael Brown',
                'customer_phone' => '+1234567892',
                'customer_email' => null,
                'reservation_date' => now()->addDays(3),
                'reservation_time' => '20:00:00',
                'number_of_people' => 6,
                'table_id' => $tables->count() > 2 ? $tables[2]->id : null,
                'status' => ReservationStatus::CONFIRMED,
                'special_request' => 'Birthday celebration, please arrange birthday cake',
                'deposit_amount' => 100.00,
                'payment_status' => PaymentStatus::PAID,
                'duration_minutes' => 150,
            ],
            [
                'customer_name' => 'Sarah Davis',
                'customer_phone' => '+1234567893',
                'customer_email' => 'sarah.davis@example.com',
                'reservation_date' => now()->subDays(1),
                'reservation_time' => '19:00:00',
                'number_of_people' => 3,
                'table_id' => $tables->count() > 3 ? $tables[3]->id : null,
                'status' => ReservationStatus::COMPLETED,
                'special_request' => null,
                'deposit_amount' => 30.00,
                'payment_status' => PaymentStatus::PAID,
                'duration_minutes' => 120,
                'check_in_time' => now()->subDays(1)->setTime(19, 5, 0),
                'check_out_time' => now()->subDays(1)->setTime(21, 0, 0),
            ],
            [
                'customer_name' => 'Robert Wilson',
                'customer_phone' => '+1234567894',
                'customer_email' => 'robert.w@example.com',
                'reservation_date' => now()->subDays(2),
                'reservation_time' => '18:30:00',
                'number_of_people' => 2,
                'table_id' => $tables->count() > 4 ? $tables[4]->id : null,
                'status' => ReservationStatus::NO_SHOW,
                'special_request' => 'Quiet area preferred',
                'deposit_amount' => 25.00,
                'payment_status' => PaymentStatus::PARTIAL,
                'duration_minutes' => 90,
            ],
            [
                'customer_name' => 'Lisa Anderson',
                'customer_phone' => '+1234567895',
                'customer_email' => 'lisa.anderson@example.com',
                'reservation_date' => now()->subDays(3),
                'reservation_time' => '20:00:00',
                'number_of_people' => 4,
                'table_id' => $tables->isNotEmpty() ? $tables[0]->id : null,
                'status' => ReservationStatus::CANCELLED,
                'special_request' => 'Outdoor seating if available',
                'deposit_amount' => 0.00,
                'payment_status' => PaymentStatus::UNPAID,
                'duration_minutes' => 120,
                'cancel_reason' => 'Customer changed plans, will reschedule later',
            ],
            [
                'customer_name' => 'David Martinez',
                'customer_phone' => '+1234567896',
                'customer_email' => 'david.m@example.com',
                'reservation_date' => now(),
                'reservation_time' => '19:00:00',
                'number_of_people' => 5,
                'table_id' => $tables->count() > 1 ? $tables[1]->id : null,
                'status' => ReservationStatus::CHECKED_IN,
                'special_request' => 'Child seat needed',
                'deposit_amount' => 40.00,
                'payment_status' => PaymentStatus::PARTIAL,
                'duration_minutes' => 120,
                'check_in_time' => now()->setTime(19, 10, 0),
            ],
        ];

        foreach ($reservations as $reservationData) {
            // Generate unique code
            $reservationData['reservation_code'] = Reservation::generateReservationCode($branch ? $branch->id : null);
            $reservationData['created_by'] = $user->id;
            $reservationData['branch_id'] = $branch ? $branch->id : null;
            $reservationData['reminder_sent'] = false;

            Reservation::create($reservationData);
        }

        $this->command->info('Reservation seeder completed successfully!');
        $this->command->info('Created ' . count($reservations) . ' sample reservations.');
    }
}
