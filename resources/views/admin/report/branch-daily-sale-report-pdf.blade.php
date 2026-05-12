<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('Branch Daily Sale Report') }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            margin: 0;
            padding: 20px;
            color: #333;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #333;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .company-name {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .report-title {
            font-size: 18px;
            color: #666;
            margin-bottom: 5px;
        }
        .report-date {
            font-size: 12px;
            color: #888;
        }
        .summary-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            font-size: 9px;
        }
        .summary-table th,
        .summary-table td {
            border: 1px solid #ddd;
            padding: 6px;
            text-align: center;
        }
        .summary-table th {
            background-color: #f8f9fa;
            font-weight: bold;
            text-align: center;
            color: #333;
        }
        .branch-name {
            text-align: left !important;
            font-weight: bold;
            background-color: #f8f9fa;
            min-width: 100px;
        }
        .currency-amount {
            text-align: right;
            font-weight: 500;
        }
        .total-cell {
            background-color: #e8f5e8;
            font-weight: bold;
        }
        .no-data {
            text-align: center;
            color: #999;
            font-style: italic;
            padding: 40px;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #888;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
        .date-range {
            text-align: center;
            margin-bottom: 20px;
            font-size: 14px;
            color: #555;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="company-name">{{ $company['company_name'] ?? 'Restaurant POS' }}</div>
        <div class="report-title">{{ __('Branch Daily Sale Report') }}</div>
        <div class="report-date">{{ __('Generated on') }}: {{ now()->format('F d, Y h:i A') }}</div>
    </div>

    <div class="date-range">
        {{ __('Period') }}: {{ \Carbon\Carbon::parse($dateRange[0]['date'])->format('M j, Y') }} - {{ \Carbon\Carbon::parse(end($dateRange)['date'])->format('M j, Y') }}
    </div>

    @if(count($summary) > 0)
        <table class="summary-table">
            <thead>
                <!-- Main header row -->
                <tr>
                    <th class="branch-name" rowspan="{{ count($availableCurrencies) > 1 ? 2 : 1 }}">{{ __('Branch') }}</th>
                    @foreach($dateRange as $dateInfo)
                        <th colspan="{{ count($availableCurrencies) }}">{{ $dateInfo['label'] }}</th>
                    @endforeach
                    <th class="total-cell" colspan="{{ count($availableCurrencies) }}">{{ __('Total') }}</th>
                </tr>
                <!-- Currency sub-header row (only if multiple currencies) -->
                @if(count($availableCurrencies) > 1)
                    <tr>
                        @foreach($dateRange as $dateInfo)
                            @foreach($availableCurrencies as $currency)
                                <th style="font-size: 8px;">{{ $currency }}</th>
                            @endforeach
                        @endforeach
                        @foreach($availableCurrencies as $currency)
                            <th class="total-cell" style="font-size: 8px;">{{ $currency }}</th>
                        @endforeach
                    </tr>
                @endif
            </thead>
            <tbody>
                @foreach($summary as $branch)
                    <tr>
                        <td class="branch-name">{{ $branch['branch_name'] }}</td>
                        @foreach($dateRange as $dateInfo)
                            @foreach($availableCurrencies as $currency)
                                <td class="currency-amount">
                                    @php
                                        $dailyData = $branch['daily_data'][$dateInfo['date']] ?? null;
                                        $amount = 0;
                                        if ($dailyData && isset($dailyData['amounts'][$currency])) {
                                            $amount = $dailyData['amounts'][$currency];
                                        }
                                    @endphp
                                    @if($amount > 0)
                                        {{ number_format($amount, 2) }}
                                    @else
                                        --
                                    @endif
                                </td>
                            @endforeach
                        @endforeach
                        @foreach($availableCurrencies as $currency)
                            <td class="currency-amount total-cell">
                                @php
                                    $totalAmount = $branch['total_amounts'][$currency] ?? 0;
                                @endphp
                                {{ number_format($totalAmount, 2) }}
                            </td>
                        @endforeach
                    </tr>
                @endforeach
                <!-- Grand Total Row -->
                <tr style="background-color: #16a34a; color: white; font-weight: bold;">
                    <td class="branch-name" style="background-color: #16a34a; color: white; font-weight: bold;">{{ __('Grand Total') }}</td>
                    @foreach($dateRange as $dateInfo)
                        @foreach($availableCurrencies as $currency)
                            <td class="currency-amount" style="background-color: #16a34a; color: white; font-weight: bold; text-align: center;">
                                @php
                                    $dailyGrandTotal = 0;
                                    foreach ($summary as $branch) {
                                        if (isset($branch['daily_data'][$dateInfo['date']]['amounts'][$currency])) {
                                            $dailyGrandTotal += $branch['daily_data'][$dateInfo['date']]['amounts'][$currency];
                                        }
                                    }
                                @endphp
                                @if($dailyGrandTotal > 0)
                                    {{ number_format($dailyGrandTotal, 2) }}
                                @else
                                    --
                                @endif
                            </td>
                        @endforeach
                    @endforeach
                    @foreach($availableCurrencies as $currency)
                        <td class="currency-amount" style="background-color: #16a34a; color: white; font-weight: bold; text-align: center;">
                            @php
                                $finalGrandTotal = 0;
                                foreach ($summary as $branch) {
                                    if (isset($branch['total_amounts'][$currency])) {
                                        $finalGrandTotal += $branch['total_amounts'][$currency];
                                    }
                                }
                            @endphp
                            {{ number_format($finalGrandTotal, 2) }}
                        </td>
                    @endforeach
                </tr>
            </tbody>
        </table>
    @else
        <div class="no-data">
            {{ __('No data available for the selected period') }}
        </div>
    @endif

    <div class="footer">
        {{ __('This report was generated automatically by the Restaurant POS System') }}
    </div>
</body>
</html>
