<?php

namespace App\Exports;

use App\Http\Requests\PaginateRequest;
use App\Libraries\AppLibrary;
use App\Services\DailySaleSummaryReportService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class DailySaleSummaryReportExport implements FromCollection, WithHeadings, WithStyles, WithColumnWidths, WithTitle
{
    protected DailySaleSummaryReportService $dailySaleSummaryReportService;
    protected PaginateRequest $request;

    public function __construct(DailySaleSummaryReportService $dailySaleSummaryReportService, PaginateRequest $request)
    {
        $this->dailySaleSummaryReportService = $dailySaleSummaryReportService;
        $this->request = $request;
    }

    public function collection(): Collection
    {
        $items = $this->dailySaleSummaryReportService->list($this->request);
        $rows = collect();

        // Get date range
        $requests = $this->request->all();
        $dateFrom = isset($requests['from_date'])
            ? \App\Libraries\AppLibrary::datetime(\App\Libraries\AppLibrary::filterDateTime($requests['from_date']))
            : 'N/A';
        $dateTo = isset($requests['to_date'])
            ? \App\Libraries\AppLibrary::datetime(\App\Libraries\AppLibrary::filterDateTime($requests['to_date']))
            : 'N/A';

        // Get the first (and only) item from the collection
        $data = !empty($items) && isset($items[0]) ? $items[0] : null;

        if (!$data) {
            $rows->push(['No data available']);
            return $rows;
        }

        $totalInvoice = intval($data->total_invoices ?? 0);
        $voidInvoice = intval($data->void_invoice ?? 0);
        $totalVoidItemOrder = intval($data->deleted_order_items ?? 0);

        $totalRevenue = floatval($data->total_revenue ?? 0);
        $totalDiscount = floatval($data->total_discount ?? 0);
        $saleItems = $data->sale_items_by_printer ?? [];
        $paymentMethods = $data->payment_methods ?? [];
 
        // Convert Collection to array if needed
        if (is_object($saleItems) && method_exists($saleItems, 'toArray')) {
            $saleItems = $saleItems->toArray();
        }
        if (is_object($paymentMethods) && method_exists($paymentMethods, 'toArray')) {
            $paymentMethods = $paymentMethods->toArray();
        } 
        $rows->push(['Daily Sale Report', '', '', '']);
        $rows->push([]);

        // Header Information
        $rows->push(['Cashier', '', '', Auth::user()->name ?? '']);
        $rows->push(['Start Date', '', '', $dateFrom]);
        $rows->push(['End Date', '', '', $dateTo]);

        // Invoice Summary
        $rows->push(['Invoice Summary', '', '', '']);
        $rows->push([trans('all.label.total_invoice'), '', '', $totalInvoice]);
        $rows->push([trans('all.label.total_void_invoice'), '', '', $voidInvoice]);
        $rows->push([trans('all.label.total_void_item_order'), '', '', $totalVoidItemOrder]);


        // Total Sale Items with 4-column layout
        $rows->push([trans('all.label.total_sale_items'), trans('all.label.name'), trans('all.label.total_item'), trans('all.label.amount')]);
        if (is_array($saleItems) && !empty($saleItems)) {
            foreach ($saleItems as $item) {
                if (is_array($item)) {
                    $rows->push([
                        '',
                        $item['printer_name'] ?? '',
                        $item['total_quantity'] ?? 0,
                        AppLibrary::flatAmountFormat($item['total_price'] ?? 0)
                    ]);
                }
            }
        } else {
            $rows->push(['No data', '', '0', '0.00']);
        }

        // Financial Summary
        $rows->push([trans('all.label.financial_summary'), '', '', '']);
        $rows->push([trans('all.label.total_revenue'), '', '', AppLibrary::flatAmountFormat($totalRevenue)]);
        $rows->push([trans('all.label.total_discount'), '', '', AppLibrary::flatAmountFormat($totalDiscount)]);
        $rows->push(['Total', '', '', AppLibrary::flatAmountFormat($totalRevenue - $totalDiscount)]);

        // Net Sale - Payment Method
        $rows->push([trans('all.label.net_sale') . ' - ' . trans('all.label.payment_method'), '', '', '']);
        if (is_array($paymentMethods) && !empty($paymentMethods)) {
            foreach ($paymentMethods as $item) {
                if (is_array($item)) {
                    $rows->push([
                        $item['method_name'] ?? 'Unknown',
                        '',
                        '',
                        AppLibrary::flatAmountFormat($item['amount'] ?? 0)
                    ]);
                }
            }
        } else {
            $rows->push(['No data', '', '', '0.00']);
        }

        return $rows;
    }

    public function headings(): array
    {
        // No fixed heading row; we're manually building all rows
        return [];
    }

    public function styles(Worksheet $sheet)
    {
        $highestRow = $sheet->getHighestRow();
        $highestColumn = $sheet->getHighestColumn();

        // Apply borders to all cells
        $sheet->getStyle('A1:' . $highestColumn . $highestRow)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['argb' => '000000'],
                ],
            ],
        ]);

        // Center the title
        $sheet->getStyle('A1:D1')->applyFromArray([
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
            ],
            'font' => [
                'bold' => true,
                'size' => 14,
            ],
        ]);

        // Merge title cells
        $sheet->mergeCells('A1:D1');

        // Bold section headers
        $sectionRows = [3, 6, 9]; // Cashier, Invoice Summary, Total Sale Items
        $currentRow = 3;

        // Find and style section headers dynamically
        for ($row = 1; $row <= $highestRow; $row++) {
            $cellValue = $sheet->getCell('A' . $row)->getValue();
            if (in_array($cellValue, ['Cashier', 'Invoice Summary', 'Total Sale Items', 'Financial Summary', 'Net Sale - Payment Method'])) {
                $sheet->getStyle('A' . $row . ':D' . $row)->applyFromArray([
                    'font' => [
                        'bold' => true,
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => [
                            'argb' => 'FFF1F5F9',
                        ],
                    ],
                ]);
            }

            // Bold Total rows
            if (in_array($cellValue, ['Total'])) {
                $sheet->getStyle('A' . $row . ':D' . $row)->applyFromArray([
                    'font' => [
                        'bold' => true,
                    ],
                ]);
            }
        }

        // Right align values in column C
        $sheet->getStyle('C1:C' . $highestRow)->applyFromArray([
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_RIGHT,
            ],
        ]);

        // Right align values in column D
        $sheet->getStyle('D1:D' . $highestRow)->applyFromArray([
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_RIGHT,
            ],
        ]);

        return [];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 25,
            'B' => 20,
            'C' => 15,
            'D' => 20,
        ];
    }

    public function title(): string
    {
        return 'Daily Sale Report';
    }
}
