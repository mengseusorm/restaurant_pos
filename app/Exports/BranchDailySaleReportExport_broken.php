<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Illuminate\Support\Facades\Log;

class BranchDailySaleReportExport_broken implements FromCollection, WithHeadings, WithStyles, WithColumnWidths
{
    private $branches;
    private $company;
    private $dateRange;
    private $availableCurrencies;

    public function __construct($branches, $company, $dateRange, $availableCurrencies = null)
    {
        // Log incoming data for debugging
        Log::info('BranchDailySaleReportExport constructor called', [
            'branches_type' => gettype($branches),
            'branches_count' => is_array($branches) ? count($branches) : 'not_array',
            'company_type' => gettype($company),
            'dateRange_type' => gettype($dateRange),
            'dateRange_count' => is_array($dateRange) ? count($dateRange) : 'not_array',
            'availableCurrencies_type' => gettype($availableCurrencies),
            'availableCurrencies_value' => $availableCurrencies
        ]);

        $this->branches = is_array($branches) ? $branches : [];
        $this->company = is_array($company) ? $company : [];
        $this->dateRange = is_array($dateRange) ? $dateRange : [];

        // Use provided currencies or extract from branch data
        if ($availableCurrencies && is_array($availableCurrencies) && !empty($availableCurrencies)) {
            $this->availableCurrencies = array_values($availableCurrencies); // Ensure it's a simple array
        } else {
            // Safely extract currencies from first branch data
            $this->availableCurrencies = ['USD']; // Default fallback

            if (!empty($this->branches) && is_array($this->branches) && isset($this->branches[0]['total_amounts']) && is_array($this->branches[0]['total_amounts'])) {
                $currencies = array_keys($this->branches[0]['total_amounts']);
                if (!empty($currencies)) {
                    $this->availableCurrencies = array_values($currencies); // Ensure it's a simple array
                }
            }
        }

        Log::info('BranchDailySaleReportExport constructor final data', [
            'final_currencies' => $this->availableCurrencies,
            'branches_count' => count($this->branches),
            'dateRange_count' => count($this->dateRange)
        ]);
    }

    public function collection()
    {
        try {
            Log::info('Collection method started');

            $data = collect();

            // Ensure branches is a proper array
            if (empty($this->branches) || !is_array($this->branches)) {
                Log::info('No branches data available');
                return $data;
            }

            Log::info('Processing branches', ['count' => count($this->branches)]);

            foreach ($this->branches as $index => $branch) {
                Log::info('Processing branch', ['index' => $index, 'branch_name' => $branch['branch_name'] ?? 'unknown']);

                // Ensure branch is an array and has required keys
                if (!is_array($branch) || !isset($branch['branch_name'])) {
                    Log::warning('Invalid branch data', ['index' => $index]);
                    continue;
                }

                $row = [$branch['branch_name']];

                // Add daily amounts for each date, separated by currency
                foreach ($this->dateRange as $dateIndex => $dateInfo) {
                    // Ensure dateInfo is an array and has required keys
                    if (!is_array($dateInfo) || !isset($dateInfo['date']) || empty($dateInfo['date'])) {
                        Log::warning('Invalid date info', ['dateIndex' => $dateIndex]);
                        continue;
                    }

                    $date = $dateInfo['date'];
                    $dailyData = null;

                    if (isset($branch['daily_data']) && is_array($branch['daily_data']) && isset($branch['daily_data'][$date])) {
                        $dailyData = $branch['daily_data'][$date];
                    }

                    foreach ($this->availableCurrencies as $currencyIndex => $currency) {
                        // Ensure currency is a string and not empty
                        if (!is_string($currency) || empty($currency)) {
                            Log::warning('Invalid currency', ['currencyIndex' => $currencyIndex, 'currency' => $currency]);
                            continue;
                        }

                        $amount = 0;

                        if ($dailyData && is_array($dailyData) && isset($dailyData['amounts']) && is_array($dailyData['amounts']) && isset($dailyData['amounts'][$currency])) {
                            $amount = $dailyData['amounts'][$currency];
                        }

                        $row[] = number_format((float)$amount, 2);
                    }
                }

                // Add total columns for each currency
                foreach ($this->availableCurrencies as $currencyIndex => $currency) {
                    // Ensure currency is a string and not empty
                    if (!is_string($currency) || empty($currency)) {
                        Log::warning('Invalid total currency', ['currencyIndex' => $currencyIndex, 'currency' => $currency]);
                        continue;
                    }

                    $totalAmount = 0;

                    if (isset($branch['total_amounts']) && is_array($branch['total_amounts']) && isset($branch['total_amounts'][$currency])) {
                        $totalAmount = $branch['total_amounts'][$currency];
                    }

                    $row[] = number_format((float)$totalAmount, 2);
                }

                $data->push($row);
            }

            // Add Grand Total Row
            if (!empty($this->branches) && is_array($this->branches)) {
                $grandTotalRow = ['Grand Total'];

                // Calculate daily grand totals for each date and currency
                foreach ($this->dateRange as $dateInfo) {
                    // Ensure dateInfo is an array and has required keys
                    if (!is_array($dateInfo) || !isset($dateInfo['date']) || empty($dateInfo['date'])) {
                        continue;
                    }

                    $date = $dateInfo['date'];

                    foreach ($this->availableCurrencies as $currency) {
                        // Ensure currency is a string and not empty
                        if (!is_string($currency) || empty($currency)) {
                            continue;
                        }

                        $dailyGrandTotal = 0;

                        // Sum up all branches for this date and currency
                        foreach ($this->branches as $branch) {
                            if (!is_array($branch)) {
                                continue;
                            }

                            if (isset($branch['daily_data']) && is_array($branch['daily_data']) &&
                                isset($branch['daily_data'][$date]) && is_array($branch['daily_data'][$date]) &&
                                isset($branch['daily_data'][$date]['amounts']) && is_array($branch['daily_data'][$date]['amounts']) &&
                                isset($branch['daily_data'][$date]['amounts'][$currency])) {
                                $dailyGrandTotal += (float)$branch['daily_data'][$date]['amounts'][$currency];
                            }
                        }

                        $grandTotalRow[] = number_format($dailyGrandTotal, 2);
                    }
                }

                // Calculate final grand totals for each currency
                foreach ($this->availableCurrencies as $currency) {
                    // Ensure currency is a string and not empty
                    if (!is_string($currency) || empty($currency)) {
                        continue;
                    }

                    $finalGrandTotal = 0;

                    foreach ($this->branches as $branch) {
                        if (!is_array($branch)) {
                            continue;
                        }

                        if (isset($branch['total_amounts']) && is_array($branch['total_amounts']) && isset($branch['total_amounts'][$currency])) {
                            $finalGrandTotal += (float)$branch['total_amounts'][$currency];
                        }
                    }

                    $grandTotalRow[] = number_format($finalGrandTotal, 2);
                }

                $data->push($grandTotalRow);
            }

            Log::info('Collection method completed successfully', ['rows_count' => $data->count()]);
            return $data;

        } catch (\Exception $e) {
            Log::error('Exception in collection method: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            throw $e;
        }
    }
                        }

                        if (isset($branch['daily_data']) && is_array($branch['daily_data']) &&
                            isset($branch['daily_data'][$date]) && is_array($branch['daily_data'][$date]) &&
                            isset($branch['daily_data'][$date]['amounts']) && is_array($branch['daily_data'][$date]['amounts']) &&
                            isset($branch['daily_data'][$date]['amounts'][$currency])) {
                            $dailyGrandTotal += (float)$branch['daily_data'][$date]['amounts'][$currency];
                        }
                    }

                    $grandTotalRow[] = number_format($dailyGrandTotal, 2);
                }
            }

            // Calculate final grand totals for each currency
            foreach ($this->availableCurrencies as $currency) {
                // Ensure currency is a string and not empty
                if (!is_string($currency) || empty($currency)) {
                    continue;
                }

                $finalGrandTotal = 0;

                foreach ($this->branches as $branch) {
                    if (!is_array($branch)) {
                        continue;
                    }

                    if (isset($branch['total_amounts']) && is_array($branch['total_amounts']) && isset($branch['total_amounts'][$currency])) {
                        $finalGrandTotal += (float)$branch['total_amounts'][$currency];
                    }
                }

                $grandTotalRow[] = number_format($finalGrandTotal, 2);
            }

            $data->push($grandTotalRow);
        }

        return $data;
    }

    public function headings(): array
    {
        // For Excel export, we'll combine them into single headers
        $combinedHeaders = ['Branch'];

        // Ensure dateRange is a proper array
        if (!is_array($this->dateRange)) {
            $this->dateRange = [];
        }

        // Ensure availableCurrencies is a proper array
        if (!is_array($this->availableCurrencies)) {
            $this->availableCurrencies = ['USD'];
        }

        foreach ($this->dateRange as $dateInfo) {
            // Ensure dateInfo is an array and has the required keys
            if (!is_array($dateInfo) || !isset($dateInfo['label']) || empty($dateInfo['label'])) {
                continue;
            }

            foreach ($this->availableCurrencies as $currency) {
                // Ensure currency is a string and not empty
                if (!is_string($currency) || empty($currency)) {
                    continue;
                }
                $combinedHeaders[] = $dateInfo['label'] . ' (' . $currency . ')';
            }
        }

        foreach ($this->availableCurrencies as $currency) {
            // Ensure currency is a string and not empty
            if (!is_string($currency) || empty($currency)) {
                continue;
            }
            $combinedHeaders[] = $currency;
        }

        return $combinedHeaders;
    }

    public function styles(Worksheet $sheet)
    {
        // Safely calculate total columns
        $dateRangeCount = is_array($this->dateRange) ? count($this->dateRange) : 0;
        $currenciesCount = is_array($this->availableCurrencies) ? count($this->availableCurrencies) : 1;
        $branchesCount = is_array($this->branches) ? count($this->branches) : 0;

        $totalColumns = ($dateRangeCount * $currenciesCount) + $currenciesCount + 1;
        $lastColumnLetter = chr(64 + $totalColumns);

        // Header row styling
        $sheet->getStyle('A1:' . $lastColumnLetter . '1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'color' => ['rgb' => '4F46E5'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'],
                ],
            ],
        ]);

        // Data rows styling (only if we have data)
        if ($branchesCount > 0) {
            // Include grand total row in count
            $lastRow = $branchesCount + 2; // +1 for header, +1 for grand total row

            $sheet->getStyle('A2:' . $lastColumnLetter . ($lastRow - 1))->applyFromArray([
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => 'CCCCCC'],
                    ],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ]);

            // Branch name column alignment
            $sheet->getStyle('A2:A' . ($lastRow - 1))->applyFromArray([
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_LEFT,
                ],
                'font' => [
                    'bold' => true,
                ],
            ]);

            // Grand Total row styling
            $grandTotalRowNumber = $lastRow;
            $sheet->getStyle('A' . $grandTotalRowNumber . ':' . $lastColumnLetter . $grandTotalRowNumber)->applyFromArray([
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF'],
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'color' => ['rgb' => '16A34A'],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THICK,
                        'color' => ['rgb' => '15803D'],
                    ],
                ],
            ]);

            // Total columns styling (last currencies count columns) - excluding grand total row
            if ($currenciesCount > 0) {
                $totalStartColumn = chr(64 + $totalColumns - $currenciesCount + 1);
                $sheet->getStyle($totalStartColumn . '2:' . $lastColumnLetter . ($lastRow - 1))->applyFromArray([
                    'font' => [
                        'bold' => true,
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'color' => ['rgb' => 'F3F4F6'],
                    ],
                ]);
            }
        }

        return [];
    }

    public function columnWidths(): array
    {
        $widths = ['A' => 20]; // Branch name column

        // Calculate total columns needed
        $dateRangeCount = is_array($this->dateRange) ? count($this->dateRange) : 0;
        $currenciesCount = is_array($this->availableCurrencies) ? count($this->availableCurrencies) : 1;
        $totalColumns = ($dateRangeCount * $currenciesCount) + $currenciesCount;

        // Date and total columns (currency-specific columns)
        for ($i = 1; $i <= $totalColumns; $i++) {
            $columnLetter = chr(65 + $i);
            $widths[$columnLetter] = 12;
        }

        return $widths;
    }
}
