<?php

namespace App\Exports;

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

class ItemOrderDeletedExport implements FromCollection, WithHeadings, WithStyles
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
        $itemArray = [];
        $deletedItemsArray = $this->orderService->listOrderItemDeleted($this->request);

        // Get date range for header
        $fromDate = $this->request->get('from_date') 
            ? \Carbon\Carbon::parse($this->request->get('from_date'))->format('m/d/Y, h:i A')
            : \Carbon\Carbon::now()->subDay()->format('m/d/Y, h:i A');

        $toDate = $this->request->get('to_date')
            ? \Carbon\Carbon::parse($this->request->get('to_date'))->format('m/d/Y, h:i A')  
            : \Carbon\Carbon::now()->format('m/d/Y, h:i A');

        // Header rows
        $itemArray[] = [
            'Deleted Order Items Report',
            '', '', '', '', '', '', ''
        ];

        $itemArray[] = [
            'From: ' . $fromDate,
            'To: ' . $toDate,
            '', '', '', '', '', ''
        ];

        $itemArray[] = ['', '', '', '', '', '', '', ''];

        // Column headers
        $itemArray[] = [
            trans('all.label.order_serial_no'),
            trans('all.label.item_name'),
            trans('all.label.quantity'),
            trans('all.label.price'),
            trans('all.label.discount'),   
            trans('all.label.tax'), 
            trans('all.label.total_price'),  
            trans('all.label.delete_reason'), 
        ];

        foreach ($deletedItemsArray as $item) {
            $itemArray[] = [
                $item->order_serial_no,
                $item->item->name ?? '',
                $item->quantity,
                AppLibrary::flatAmountFormat($item->price),
                AppLibrary::flatAmountFormat($item->discount),
                AppLibrary::flatAmountFormat($item->tax_amount),
                AppLibrary::flatAmountFormat($item->total_price),
                $item->delete_reason ?? '',
            ];
        }
        
        return collect($itemArray);
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
            // Header row 1 - "Deleted Order Items Report"
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
        $sheet->getColumnDimension('B')->setWidth(25); // Item Name
        $sheet->getColumnDimension('C')->setWidth(8);  // Quantity
        $sheet->getColumnDimension('D')->setWidth(12); // Price
        $sheet->getColumnDimension('E')->setWidth(12); // Discount
        $sheet->getColumnDimension('F')->setWidth(12); // Tax Amount
        $sheet->getColumnDimension('G')->setWidth(12); // Total Price
        $sheet->getColumnDimension('H')->setWidth(20); // Delete Reason

        // Set row height for header rows
        $sheet->getRowDimension(1)->setRowHeight(25);
        $sheet->getRowDimension(2)->setRowHeight(20);
        $sheet->getRowDimension(4)->setRowHeight(20);

        return $styles;
    }
}
