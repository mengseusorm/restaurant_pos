<?php

namespace Database\Seeders;

use App\Models\Room;
use Illuminate\Database\Seeder;

class RoomSeeder extends Seeder
{
    public array $rooms = [
        ['name' => 'Room 1', 'branch_id' => 1],
        ['name' => 'Room 2', 'branch_id' => 1],
        ['name' => 'Room 3', 'branch_id' => 1],
        ['name' => 'Room 4', 'branch_id' => 1],
        ['name' => 'VIP Room 1', 'branch_id' => 1],
        ['name' => 'VIP Room 2', 'branch_id' => 1],
    ];

    public function run(): void
    {
        foreach ($this->rooms as $room) {
            Room::create([
                'name'           => $room['name'],
                'branch_id'      => $room['branch_id'],
                'status'         => 'available',
                'qr_code_token'  => Room::generateQrToken(),
            ]);
        }
    }
}
