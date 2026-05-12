<?php

namespace App\Exports;

use App\Libraries\AppLibrary;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;

class BranchDailySaleReportExport implements FromCollection, WithHeadings
{
    private $branches;
    private $company;
    private $dateRange;
    private $availableCurrencies;

    public function __construct($branches, $company, $dateRange, $availableCurrencies = null)
    {
        $this->branches = $branches;
        $this->company = $company;
        $this->dateRange = $dateRange;
        $this->availableCurrencies = $availableCurrencies ?: ['USD'];
    }

    public function collection(): \Illuminate\Support\Collection
    {
        $branchDailySaleReportArray = [];

        foreach ($this->branches as $branch) {
            $row = [$branch['branch_name']];

            // Add daily amounts for each date, separated by currency
            foreach ($this->dateRange as $dateInfo) {
                $date = $dateInfo['date'];
                $dailyData = $branch['daily_data'][$date] ?? null;

                foreach ($this->availableCurrencies as $currency) {
                    $amount = 0;
                    if ($dailyData && isset($dailyData['amounts'][$currency])) {
                        $amount = $dailyData['amounts'][$currency];
                    }
                    $row[] = AppLibrary::flatAmountFormat($amount);
                }
            }

            // Add total columns for each currency
            foreach ($this->availableCurrencies as $currency) {
                $totalAmount = $branch['total_amounts'][$currency] ?? 0;
                $row[] = AppLibrary::flatAmountFormat($totalAmount);
            }

            $branchDailySaleReportArray[] = $row;
        }

        // Add Grand Total Row
        if (!empty($this->branches)) {
            $grandTotalRow = ['Grand Total'];
            
            // Calculate daily grand totals for each date and currency
            foreach ($this->dateRange as $dateInfo) {
                $date = $dateInfo['date'];
                
                foreach ($this->availableCurrencies as $currency) {
                    $dailyGrandTotal = 0;
                    
                    // Sum up all branches for this date and currency
                    foreach ($this->branches as $branch) {
                        if (isset($branch['daily_data'][$date]['amounts'][$currency])) {
                            $dailyGrandTotal += $branch['daily_data'][$date]['amounts'][$currency];
                        }
                    }
                    
                    $grandTotalRow[] = AppLibrary::flatAmountFormat($dailyGrandTotal);
                }
            }
            
            // Calculate final grand totals for each currency
            foreach ($this->availableCurrencies as $currency) {
                $finalGrandTotal = 0;
                
                foreach ($this->branches as $branch) {
                    if (isset($branch['total_amounts'][$currency])) {
                        $finalGrandTotal += $branch['total_amounts'][$currency];
                    }
                }
                
                $grandTotalRow[] = AppLibrary::flatAmountFormat($finalGrandTotal);
            }
            
            $branchDailySaleReportArray[] = $grandTotalRow;
        }

        return collect($branchDailySaleReportArray);
    }

    public function headings(): array
    {
        $headers = [trans('all.label.branch')];
        
        foreach ($this->dateRange as $dateInfo) {
            foreach ($this->availableCurrencies as $currency) {
                $headers[] = $dateInfo['label'] . ' (' . $currency . ')';
            }
        }
        
        foreach ($this->availableCurrencies as $currency) {
            $headers[] = trans('all.label.total') . ' (' . $currency . ')';
        }

        return $headers;
    }
}
