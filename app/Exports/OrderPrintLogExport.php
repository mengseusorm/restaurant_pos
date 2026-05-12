<?php

namespace App\Exports;

use App\Models\OrderPrintLog;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class OrderPrintLogExport implements FromCollection, WithHeadings, WithMapping
{
    protected $orderPrintLogs;

    public function __construct($orderPrintLogs)
    {
        $this->orderPrintLogs = $orderPrintLogs;
    }

    public function collection()
    {
        return $this->orderPrintLogs;
    }

    public function headings(): array
    {
        return [
            'ID',
            'User ID',
            'Branch ID',
            'Order Serial Number',
            'Print Type',
            'Print Success',
            'Error Message',
            'Created Date',
        ];
    }

    public function map($orderPrintLog): array
    {
        $printTypeName = '';
        switch ($orderPrintLog->print_type) {
            case 5:
                $printTypeName = 'Menu';
                break;
            case 10:
                $printTypeName = 'Invoice';
                break;
            case 15:
                $printTypeName = 'Bill';
                break;
            default:
                $printTypeName = 'Unknown';
        }

        return [
            $orderPrintLog->id,
            $orderPrintLog->user_id,
            $orderPrintLog->branch_id,
            $orderPrintLog->order_serial_number,
            $printTypeName,
            $orderPrintLog->print_success ? 'Success' : 'Failed',
            $orderPrintLog->error_message ?? '',
            $orderPrintLog->created_at->format('Y-m-d H:i:s'),
        ];
    }
}
