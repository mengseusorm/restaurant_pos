<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ trans('all.label.sales_summary_report') }}</title>
    <style>
        body {
            font-family: "DejaVu Sans", sans-serif;
            margin: 40px;
            color: #000;
        }

        .date-range {
            display: flex;
            justify-content: space-between;
            margin-bottom: 40px;
            font-size: 18px;
            font-weight: bold;
        }

        .report-title {
            font-size: 36px;
            font-weight: bold;
            margin: 40px 0;
        }

        .section-title {
            font-size: 24px;
            font-weight: bold;
            margin: 30px 0 20px 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 40px;
        }

        th, td {
            border: 1px solid #000;
            padding: 10px;
            text-align: left;
        }

        th {
            background-color: #f0f0f0;
        }

        .amount {
            text-align: right;
        }
    </style>
</head>

<body>
    <?php
        $data = [];
        foreach ($orders as $order) {
            $data[] = $order;
        }
    ?>

   <div class="date-range">
        <div>Date From: {{ $fromDate }}</div>
        <div>Date To: {{ $toDate }}</div>
    </div>

    <div class="report-title">Sale Summary Report</div>

    <div class="section-title">Sales Summary</div>
    <table>
        <tr>
            <td>Total sales</td>
                @php
                    $totalSales = collect($data)->sum(fn($order) => floatval($order['subtotal'] ?? 0));
                @endphp
            <td class="amount">
                {{ App\Libraries\AppLibrary::flatAmountFormat($totalSales)}}
            </td>
        </tr>
        <tr>
            <td>VAT</td>
                @php
                    $vat = collect($data)->sum(fn($order) => floatval($order['total_tax'] ?? 0));
                @endphp
            <td class="amount">
                {{ App\Libraries\AppLibrary::flatAmountFormat($vat)}}
            </td>
        </tr>
        <tr>
            <td>Net sale</td>
            <td class="amount">
                {{ App\Libraries\AppLibrary::flatAmountFormat($totalSales - $vat)}}
            </td>
        </tr>
    </table>
    <div class="section-title">Customer & Transaction Info</div>
    <table>
        <tr>
            <td>Number of Customer</td>
                @php
                    $uniqueUserCount = $orders->unique('user_id')->count();
                @endphp
            <td class="amount">{{ $uniqueUserCount }}</td>
        </tr>
        <tr>
            <td>Settlement number (number of orders)</td>
            <td class="amount">{{ $orders->count()}}</td>
        </tr>
    </table>

    <div class="section-title">Payment Methods</div>
    @php
        $grouped = $orders->groupBy(function ($order) {
            return $order->paymentMethod->name ?? 'N/A';
        });
    @endphp

    <table>
       @foreach ($grouped as $paymentMethod => $ordersGroup)
            <tr>
                <td>{{ $paymentMethod }}</td>
                <td class="amount">
                    ${{ number_format($ordersGroup->sum('total'), 2) }}
                </td>
            </tr>
        @endforeach
    </table>
</body>

</html>
