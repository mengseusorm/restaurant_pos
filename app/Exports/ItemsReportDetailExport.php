<?php

namespace App\Exports;

use App\Enums\PaymentStatus;
use App\Libraries\AppLibrary;
use App\Services\ItemService;
use App\Http\Requests\PaginateRequest;
use App\Http\Resources\ItemDetailReportResource;
use App\Models\Item;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class ItemsReportDetailExport implements FromCollection, WithStyles
{
    public ItemService $itemService;
    public PaginateRequest $request;

    public function __construct(ItemService $itemService, $request, $isDetail = false)
    {
        $this->itemService = $itemService;
        $this->request     = $request;
    }
    public function collection(): \Illuminate\Support\Collection
    {
        try {
            $itemsReportArray = [];

            // Get branch open_time and close_time
            $branch = \App\Models\Branch::find(auth()->user()->branch_id);

            if ($this->request->get('from_date')) {
                $fromDate = \App\Libraries\AppLibrary::datetime(\App\Libraries\AppLibrary::filterDateTime($this->request->get('from_date')));
            } else {
                // Default to yesterday with branch open_time
                $fromDate = \Carbon\Carbon::now()->subDay()->startOfDay();
                if ($branch && $branch->open_time) {
                    $time = explode(':', $branch->open_time);
                    $fromDate->setTime((int)$time[0], (int)$time[1], 0);
                }
                $fromDate = \App\Libraries\AppLibrary::datetime($fromDate);
            }

            if ($this->request->get('to_date')) {
                $toDate = \App\Libraries\AppLibrary::datetime(\App\Libraries\AppLibrary::filterDateTime($this->request->get('to_date')));
            } else {
                // Default to today with branch close_time
                $toDate = \Carbon\Carbon::now()->startOfDay();
                if ($branch && $branch->close_time) {
                    $time = explode(':', $branch->close_time);
                    $toDate->setTime((int)$time[0], (int)$time[1], 59);
                } else {
                    $toDate->endOfDay();
                }
                $toDate = \App\Libraries\AppLibrary::datetime($toDate);
            }

            // Header row
            $itemsReportArray[] = [
                'Item Report Detail',
                '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', ''
            ];

            $itemsReportArray[] = [
                'From: ' . $fromDate,
                'To: ' . $toDate,
                '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', ''
            ];
            $itemsReportArray[] = ['', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', ''];

            // Column headers
            $itemsReportArray[] = [
                'Order NO.',
                'Invoice Number',
                'Invoice Date',
                'Table',
                'Item Code',
                'No',
                'Item Name',
                'Qty',
                'Price',
                'Sub Total',
                'Discount%',
                'Discount$',
                'Total',
                'Total Amount',
                'Change',
                'Rec. Dollar',
                'Rec. Riel',
                'Payment',
                'Remark'
            ];

            // Get raw data from service
            $rawOrderItems = $this->itemService->itemReportDetailList($this->request);
            
            // Transform data using the same resource as the controller
            $orderItems = collect($rawOrderItems instanceof \Illuminate\Pagination\LengthAwarePaginator ? 
                $rawOrderItems->items() : $rawOrderItems)
                ->map(function ($item) {
                    return (new ItemDetailReportResource($item))->resolve();
                });

            $previousOrderNo = null;

            foreach ($orderItems as $no => $orderItem) {
                $isFirstItemOfOrder = ($previousOrderNo !== $orderItem['order_no']);

                $itemsReportArray[] = [
                    $orderItem['order_no'] ?? '',
                    $orderItem['invoice_number'] ?? '',
                    $orderItem['invoice_date'] ?? '',
                    $orderItem['table_no'] ?? '',
                    $orderItem['item_code'] ?? '--',
                    $no + 1,
                    $orderItem['name'] ?? '',
                    $orderItem['quantity'] ?? 0,
                    $orderItem['price'] ?? '0.00',
                    $orderItem['sub_total'] ?? '0.00',
                    ($orderItem['discount_percentage'] ?? 0) . '%',
                    $orderItem['discount'] ?? '0.00',
                    $orderItem['total'] ?? '0.00',
                    $orderItem['total_amount'] ?? '0.00',
                    $isFirstItemOfOrder ? ($orderItem['change_amount'] ?? '0.00') : '',
                    $isFirstItemOfOrder ? ($orderItem['received_dollar'] ?? '0.00') : '',
                    $isFirstItemOfOrder ? ($orderItem['received_riel'] ?? '0.00') : '',
                    $orderItem['payment'] ?? 'N/A',
                    $orderItem['remark'] ?? '',
                ];

                $previousOrderNo = $orderItem['order_no'];
            }


            // Calculate totals like in Vue component
            $uniqueOrderNumbers = collect($orderItems)->pluck('order_no')->unique();
            $totalInvoiceCount = $uniqueOrderNumbers->count();
            
            // Calculate unique order totals for payment details
            $uniqueOrderTotals = [];
            $processedOrders = [];
            $paymentMethods = [];

            foreach ($orderItems as $orderItem) {
                if (!in_array($orderItem['order_no'], $processedOrders)) {
                    $uniqueOrderTotals['change_amount'] = ($uniqueOrderTotals['change_amount'] ?? 0) + (float)str_replace(',', '', $orderItem['change_amount'] ?? '0.00');
                    $uniqueOrderTotals['received_dollar'] = ($uniqueOrderTotals['received_dollar'] ?? 0) + (float)str_replace(',', '', $orderItem['received_dollar'] ?? '0.00');
                    $uniqueOrderTotals['received_riel'] = ($uniqueOrderTotals['received_riel'] ?? 0) + (float)str_replace(',', '', $orderItem['received_riel'] ?? '0.00');
                    $processedOrders[] = $orderItem['order_no'];
                    
                    // Collect payment methods
                    $payment = $orderItem['payment'] ?? 'N/A';
                    $paymentMethods[$payment] = ($paymentMethods[$payment] ?? 0) + 1;
                }
            }
            
            // Calculate total quantities and amounts
            $totalQuantity = collect($orderItems)->sum(function($item) {
                return (int)($item['quantity'] ?? 0);
            });
            
            $totalPrice = collect($orderItems)->sum(function($item) {
                return (float)str_replace(',', '', $item['price'] ?? '0.00');
            });
            
            $totalSubTotal = collect($orderItems)->sum(function($item) {
                return (float)str_replace(',', '', $item['sub_total'] ?? '0.00');
            });
            
            $totalDiscount = collect($orderItems)->sum(function($item) {
                return (float)str_replace(',', '', $item['discount'] ?? '0.00');
            });
            
            $totalAmount = collect($orderItems)->sum(function($item) {
                return (float)str_replace(',', '', $item['total_amount'] ?? '0.00');
            });
            
            // Payment methods string
            $paymentMethodsStr = '';
            if (!empty($paymentMethods)) {
                $paymentMethodsStr = implode(', ', array_map(
                    function ($key, $value) {
                        return $key . ' = ' . $value;
                    },
                    array_keys($paymentMethods),
                    $paymentMethods
                ));
            }

            // Total row
            $itemsReportArray[] = [
                'Total Invoice = ' . $totalInvoiceCount,
                '',
                '',
                '',
                '',
                '',
                '',
                $totalQuantity,
                AppLibrary::flatAmountFormat($totalPrice),
                AppLibrary::flatAmountFormat($totalSubTotal),
                '',
                AppLibrary::flatAmountFormat($totalDiscount),
                AppLibrary::flatAmountFormat($totalAmount),
                AppLibrary::flatAmountFormat($totalAmount),
                AppLibrary::flatAmountFormat($uniqueOrderTotals['change_amount'] ?? 0),
                AppLibrary::flatAmountFormat($uniqueOrderTotals['received_dollar'] ?? 0),
                AppLibrary::flatAmountFormat($uniqueOrderTotals['received_riel'] ?? 0),
                $paymentMethodsStr,
                ''
            ];
  
            return collect($itemsReportArray);
        } catch (\Exception $exception) {
            Log::error('Error generating items report detail export: ' . $exception->getMessage()); 
            return collect([
                ['Error generating report: ' . $exception->getMessage()]
            ]);
        }
    }


    public function styles(Worksheet $sheet)
    {
        // Get the last row number to apply styles dynamically
        $lastRow = $sheet->getHighestRow();
        

        $styles = [
            // Header row 1 - "Item Report Detail"
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
        $sheet->getColumnDimension('A')->setWidth(12); // Invoice No
        $sheet->getColumnDimension('B')->setWidth(15); // Invoice Date
        $sheet->getColumnDimension('C')->setWidth(10); // Table No
        $sheet->getColumnDimension('D')->setWidth(12); // Item Code
        $sheet->getColumnDimension('E')->setWidth(6);  // No
        $sheet->getColumnDimension('F')->setWidth(25); // Menu Name
        $sheet->getColumnDimension('G')->setWidth(8);  // Qty
        $sheet->getColumnDimension('H')->setWidth(10); // Price
        $sheet->getColumnDimension('I')->setWidth(12); // Sub Total
        $sheet->getColumnDimension('J')->setWidth(10); // Discount%
        $sheet->getColumnDimension('K')->setWidth(12); // Discount$
        $sheet->getColumnDimension('L')->setWidth(10); // Total
        $sheet->getColumnDimension('M')->setWidth(12); // Total Amount
        $sheet->getColumnDimension('N')->setWidth(10); // Change
        $sheet->getColumnDimension('O')->setWidth(12); // Rec. Dollar
        $sheet->getColumnDimension('P')->setWidth(12); // Rec. Riel
        $sheet->getColumnDimension('Q')->setWidth(12); // Payment
        $sheet->getColumnDimension('R')->setWidth(20); // Remark

        // Set row height for header rows
        $sheet->getRowDimension(1)->setRowHeight(25);
        $sheet->getRowDimension(2)->setRowHeight(20);
        $sheet->getRowDimension(4)->setRowHeight(20);

        return $styles;
    }
}
