<?php

namespace App\Exports;

use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class BranchTrendReportExport implements FromArray, WithHeadings, WithStyles, WithTitle
{
    private array $summaryData;
    private array $company;
    private array $availableCurrencies;
    private array $monthsArray;

    public function __construct(array $summaryData, array $company, array $availableCurrencies)
    {
        $this->summaryData = $summaryData;
        $this->company = $company;
        $this->availableCurrencies = $availableCurrencies;
        
        // Generate month labels from the first item if available
        if (!empty($summaryData) && isset($summaryData[0]['monthly_data'])) {
            $this->monthsArray = [];
            $monthlyData = $summaryData[0]['monthly_data'];
            
            // Generate month labels based on the number of months
            $months = count($monthlyData);
            $endDate = Carbon::now();
            $startDate = Carbon::now()->subMonths($months - 1)->startOfMonth();
            $currentDate = $startDate->copy();
            
            for ($i = 0; $i < $months; $i++) {
                $this->monthsArray[] = [
                    'label' => $currentDate->format('M Y'),
                    'index' => $i
                ];
                $currentDate->addMonth();
            }
        } else {
            $this->monthsArray = [];
        }
    }

    public function array(): array
    {
        $data = [];
        
        foreach ($this->summaryData as $branch) {
            $row = [$branch['branch_name']];
            
            // Add monthly data with separate currency columns
            foreach ($this->monthsArray as $month) {
                $monthData = $branch['monthly_data'][$month['index']] ?? null;
                
                // Add separate columns for each currency
                foreach ($this->availableCurrencies as $currency) {
                    $amount = 0;
                    if ($monthData && isset($monthData['amounts'][$currency])) {
                        $amount = $monthData['amounts'][$currency];
                    }
                    $row[] = $amount > 0 ? number_format($amount, 2) : '';
                }
                
                // Orders
                $row[] = $monthData ? $monthData['orders'] : '';
            }
            
            // Total amounts with separate currency columns
            foreach ($this->availableCurrencies as $currency) {
                $totalAmount = $branch['total_amounts'][$currency] ?? 0;
                $row[] = $totalAmount > 0 ? number_format($totalAmount, 2) : '';
            }
            $row[] = $branch['total_orders'];
            
            // Average amounts with separate currency columns
            foreach ($this->availableCurrencies as $currency) {
                $avgAmount = $branch['average_amounts'][$currency] ?? 0;
                $row[] = $avgAmount > 0 ? number_format($avgAmount, 2) : '';
            }
            $row[] = $branch['average_orders'];
            
            $data[] = $row;
        }

        // Add Grand Total Row
        if (!empty($this->summaryData)) {
            $grandTotalRow = ['GRAND TOTAL'];
            
            // Calculate monthly grand totals with separate currency columns
            foreach ($this->monthsArray as $month) {
                foreach ($this->availableCurrencies as $currency) {
                    $monthlyGrandTotal = 0;
                    foreach ($this->summaryData as $branch) {
                        $monthData = $branch['monthly_data'][$month['index']] ?? null;
                        if ($monthData && isset($monthData['amounts'][$currency])) {
                            $monthlyGrandTotal += floatval($monthData['amounts'][$currency]);
                        }
                    }
                    $grandTotalRow[] = $monthlyGrandTotal > 0 ? number_format($monthlyGrandTotal, 2) : '';
                }
                
                // Monthly grand total orders
                $monthlyGrandTotalOrders = 0;
                foreach ($this->summaryData as $branch) {
                    $monthData = $branch['monthly_data'][$month['index']] ?? null;
                    if ($monthData) {
                        $monthlyGrandTotalOrders += intval($monthData['orders']);
                    }
                }
                $grandTotalRow[] = $monthlyGrandTotalOrders;
            }
            
            // Final grand totals with separate currency columns
            foreach ($this->availableCurrencies as $currency) {
                $finalGrandTotal = 0;
                foreach ($this->summaryData as $branch) {
                    $totalAmount = $branch['total_amounts'][$currency] ?? 0;
                    $finalGrandTotal += floatval($totalAmount);
                }
                $grandTotalRow[] = $finalGrandTotal > 0 ? number_format($finalGrandTotal, 2) : '';
            }
            
            // Final grand total orders
            $finalGrandTotalOrders = 0;
            foreach ($this->summaryData as $branch) {
                $finalGrandTotalOrders += intval($branch['total_orders']);
            }
            $grandTotalRow[] = $finalGrandTotalOrders;
            
            // Average grand totals with separate currency columns
            $branchCount = count($this->summaryData);
            foreach ($this->availableCurrencies as $currency) {
                $avgGrandTotal = 0;
                foreach ($this->summaryData as $branch) {
                    $avgAmount = $branch['average_amounts'][$currency] ?? 0;
                    $avgGrandTotal += floatval($avgAmount);
                }
                $avgGrandTotal = $branchCount > 0 ? ($avgGrandTotal / $branchCount) : 0;
                $grandTotalRow[] = $avgGrandTotal > 0 ? number_format($avgGrandTotal, 2) : '';
            }
            
            // Average grand total orders
            $avgGrandTotalOrders = 0;
            foreach ($this->summaryData as $branch) {
                $avgGrandTotalOrders += floatval($branch['average_orders']);
            }
            $avgGrandTotalOrders = $branchCount > 0 ? ($avgGrandTotalOrders / $branchCount) : 0;
            $grandTotalRow[] = number_format($avgGrandTotalOrders, 0);
            
            $data[] = $grandTotalRow;
        }

        return $data;
    }

    public function headings(): array
    {
        $headers = ['Branch'];
        
        // Add monthly headers with separate currency columns
        foreach ($this->monthsArray as $month) {
            foreach ($this->availableCurrencies as $currency) {
                $headers[] = $month['label'] . ' Amount (' . $currency . ')';
            }
            $headers[] = $month['label'] . ' Orders';
        }
        
        // Add total headers with separate currency columns
        foreach ($this->availableCurrencies as $currency) {
            $headers[] = 'Total Amount (' . $currency . ')';
        }
        $headers[] = 'Total Orders';
        
        // Add average headers with separate currency columns
        foreach ($this->availableCurrencies as $currency) {
            $headers[] = 'Average Amount (' . $currency . ')';
        }
        $headers[] = 'Average Orders';
        
        return $headers;
    }

    public function styles(Worksheet $sheet)
    {
        $lastRow = count($this->summaryData) + 2; // +1 for header, +1 for grand total
        
        return [
            1 => ['font' => ['bold' => true]], // Header row
            'A' => ['font' => ['bold' => true]], // Branch name column
            $lastRow => [ // Grand total row
                'font' => ['bold' => true, 'color' => ['rgb' => '374151']],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'F3F4F6']
                ],
                'borders' => [
                    'top' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 'color' => ['rgb' => 'D1D5DB']],
                    'bottom' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 'color' => ['rgb' => 'D1D5DB']]
                ]
            ]
        ];
    }

    public function title(): string
    {
        return 'Branch Trend Report';
    }
}
