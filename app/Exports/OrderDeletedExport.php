<?php

namespace App\Exports;

use App\Enums\OrderStatus;
use App\Enums\OrderType;
use App\Enums\PaymentStatus;
use App\Enums\Source;
use App\Libraries\AppLibrary;
use App\Http\Requests\PaginateRequest;
use App\Services\OrderService; 
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class OrderDeletedExport implements FromCollection, WithHeadings, WithStyles
{

    public OrderService $orderService;
    public PaginateRequest $request;

    public function __construct(OrderService $orderService, $request)
    {
        $this->orderService = $orderService;
        $this->request = $request;
    }

    public function collection(): \Illuminate\Support\Collection
    { 
        $orderArray = []; 
        $deletedOrdersArray = $this->orderService->listOrderDeleted($this->request);

        // Get date range for header
        $fromDate = $this->request->get('from_date') 
            ? \Carbon\Carbon::parse($this->request->get('from_date'))->format('m/d/Y, h:i A')
            : \Carbon\Carbon::now()->subDay()->format('m/d/Y, h:i A');

        $toDate = $this->request->get('to_date')
            ? \Carbon\Carbon::parse($this->request->get('to_date'))->format('m/d/Y, h:i A')  
            : \Carbon\Carbon::now()->format('m/d/Y, h:i A');

        // Header rows
        $orderArray[] = [
            'Deleted Orders Report',
            '', '', '', '', '', '', '', '', '', '', ''
        ];

        $orderArray[] = [
            'From: ' . $fromDate,
            'To: ' . $toDate,
            '', '', '', '', '', '', '', '', '', ''
        ];

        $orderArray[] = ['', '', '', '', '', '', '', '', '', '', '', ''];

        // Column headers
        $orderArray[] = [
            trans('all.label.order_serial_no'), 
            trans('all.label.waiting_number'),
            trans('all.label.dining_table'),
            trans('all.label.order_type'),
            trans('all.label.discount'),
            trans('all.label.amount'),
            trans('all.label.amount') . ' (VAT)',
            trans('all.label.vat'),
            trans('all.label.date'),
            trans('all.label.status'), 
            trans('all.label.source'), 
            trans('all.label.payment_status'),
        ];
 
        foreach ($deletedOrdersArray as $order) {
            $orderArray[] = [
                $order->order_serial_no,
                '#'.$order->waiting_number,  
                is_array($order->dining_table) ? implode(', ', $order->dining_table) : ($order->dining_table ? $order->dining_table : ''),
                $order->order_type == OrderType::DELIVERY ? 'DELIVERY' : ($order->order_type == OrderType::TAKEAWAY ? 'TAKEAWAY' : ($order->order_type == OrderType::POS ? 'POS' : ($order->order_type == OrderType::DINING_TABLE ? 'DINING_TABLE' : ($order->order_type == OrderType::TOKEN ? 'TOKEN' : ($order->order_type == OrderType::ONLINE_ORDER ? 'ONLINE_ORDER' : $order->order_type))))),
                AppLibrary::flatAmountFormat($order->discount),
                AppLibrary::flatAmountFormat($order->subtotal),
                AppLibrary::flatAmountFormat($order->total + $order->total_tax),
                AppLibrary::flatAmountFormat($order->total_tax),
                AppLibrary::datetime($order->order_datetime), 
                $order->status == OrderStatus::VOID ? 'Deleted/Void' : ($order->status == OrderStatus::CANCELED ? 'Canceled' : ($order->status == OrderStatus::PENDING ? 'Pending' : ($order->status == OrderStatus::ACCEPT ? 'Accepted' : ($order->status == OrderStatus::PROCESSING ? 'Processing' : ($order->status == OrderStatus::OUT_FOR_DELIVERY ? 'Out For Delivery' : ($order->status == OrderStatus::DELIVERED ? 'Delivered' : ($order->status == OrderStatus::REJECTED ? 'Rejected' : ($order->status == OrderStatus::RETURNED ? 'Returned' : trans('all.label.status' . $order->status))))))))),
                $order->source == Source::WEB ? 'WEB' : ($order->source == Source::POS ? 'POS' : ($order->source == Source::APP ? 'APP' : '')),
                $order->payment_status == PaymentStatus::PAID ? trans('all.label.paid') : ($order->payment_status == PaymentStatus::UNPAID ? trans('all.label.unpaid') : ''), 
            ];
        }
        return collect($orderArray);
    }

    public function headings(): array
    {
        return [];
    }

    public function styles(Worksheet $sheet)
    {
        // Get the last row number to apply styles dynamically
        $lastRow = $sheet->getHighestRow();

        $styles = [
            // Header row 1 - "Deleted Orders Report"
            1 => [
                'font' => [
                    'bold' => true,
                    'size' => 16,
                    'color' => ['argb' => Color::COLOR_WHITE],
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FF4472C4'], // Blue background
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ],
            // Header row 2 - Date range
            2 => [
                'font' => [
                    'bold' => true,
                    'size' => 12,
                    'color' => ['argb' => Color::COLOR_WHITE],
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FF70AD47'], // Green background
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_LEFT,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ],
            // Column headers row (row 4)
            4 => [
                'font' => [
                    'bold' => true,
                    'size' => 11,
                    'color' => ['argb' => Color::COLOR_WHITE],
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FF264653'], // Dark blue-green background
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['argb' => Color::COLOR_WHITE],
                    ],
                ],
            ],
        ];

        // Set column widths
        $sheet->getColumnDimension('A')->setWidth(15); // Order Serial No
        $sheet->getColumnDimension('B')->setWidth(12); // Waiting Number
        $sheet->getColumnDimension('C')->setWidth(15); // Dining Table
        $sheet->getColumnDimension('D')->setWidth(15); // Order Type
        $sheet->getColumnDimension('E')->setWidth(12); // Discount
        $sheet->getColumnDimension('F')->setWidth(12); // Amount
        $sheet->getColumnDimension('G')->setWidth(15); // Amount (VAT)
        $sheet->getColumnDimension('H')->setWidth(10); // VAT
        $sheet->getColumnDimension('I')->setWidth(18); // Date
        $sheet->getColumnDimension('J')->setWidth(15); // Status
        $sheet->getColumnDimension('K')->setWidth(10); // Source
        $sheet->getColumnDimension('L')->setWidth(15); // Payment Status

        // Set row height for header rows
        $sheet->getRowDimension(1)->setRowHeight(25);
        $sheet->getRowDimension(2)->setRowHeight(20);
        $sheet->getRowDimension(4)->setRowHeight(20);

        return $styles;
    }

}
