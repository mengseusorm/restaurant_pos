<?php

namespace Database\Seeders;

use App\Enums\PrinterMethodEnum;
use App\Enums\PrinterType;
use App\Models\kitchenPrinter;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PrinterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        kitchenPrinter::create([
            'id'             => 1,
            'name'           => 'printer',
            'ip'             => '192.168.1.100',
            'port'           => 9100, 
            'printer_type'   => PrinterType::PRINTINVOICE,
            'printer_method' => PrinterMethodEnum::IP,
            'branch_id'     => 1, 
            'printer_server' => 'http://127.0.0.1:5000',
            'label'          => 'CHILLY POS',
            'print_copies'   => 1 
        ]);
    }
}
