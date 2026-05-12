<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Sales Report</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: "DejaVu Sans", sans-serif;
            color: #1F1F39;
            margin: 0;
            padding: 0;
        }

        .container {
            width: 100%;
            margin: 0;
            padding: 0;
        }

        .report {
            width: 100%;
            text-align: center;
            margin-bottom: 50px;
        }

        img {
            margin: 0 0 8px 0;
            font-size: 16px;
            font-weight: 600;
        }

        p {
            margin: 0 0 16px 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px auto;
        }

        th,
        td {
            border: 1px solid #EFF0F6;
            padding: 8px;
            text-align: left;
            font-size: 10px;
            font-weight: 400;
        }

        th {
            background-color: #F8FBFB;
            font-weight: 600;
        }

        th:first-child {
            white-space: nowrap;
        }

        tbody {
            color: #6E7191;
        }

        .date,
        .time {
            white-space: nowrap;
        }

        .total {
            color: #1F1F39;
            font-weight: 600;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .footer {
            width: 100%;
            text-align: center;
            font-size: 10px;
            font-weight: 400;
            margin-top: 30px;
            padding-top: 10px;
            border-top: 1px solid #EFF0F6;
        }
    </style>
</head>

<body>
    @php
        $totalOrders = 0;
        $totalAmount = 0;
        $totalTax = 0;
        $totalWithTax = 0;
    @endphp
    <div class="container">
        <div class="report">
            <p style="margin: 0px 0px 8px 0px;font-size: 16px;font-weight: bold">{{ App\Libraries\AppLibrary::textShortener($company['company_name'], 60) }}</p>
            <p>{{ App\Libraries\AppLibrary::textShortener($company['company_address'], 60) }}</p>
            <p style="color: #ff006b;margin: 0px 0px 8px 0px;font-size: 16px;font-weight: bold;">{{ trans('all.label.user_sales_report', [], 'en') }}</p>
            <p style="margin: 0px 0px 16px 0px;font-size: 12px;">
                <strong>From:</strong> {{ $fromDate }}
                <strong style="margin-left: 20px;">To:</strong> {{ $toDate }}
            </p>
            <table>
        <thead>
            <tr>
                <th class="text-center">{{ trans('all.label.no', [], 'en') }}</th>
                <th>{{ trans('all.label.user_name', [], 'en') }}</th>
                <th class="text-right">{{ trans('all.label.total_order', [], 'en') }}</th>
                <th class="text-right">{{ trans('all.label.amount', [], 'en') }}</th>
                <th class="text-right">{{ trans('all.label.vat', [], 'en') }}</th>
                <th class="text-right">{{ trans('all.label.amount_vat', [], 'en') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach($userSales as $index => $userSale)
                @php
                    $totalOrders += $userSale->total_orders;
                    $totalAmount += $userSale->total;
                    $totalTax += $userSale->total_tax;
                    $totalWithTax += ($userSale->total + $userSale->total_tax);
                @endphp
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $userSale->user_name }}</td>
                    <td class="text-right">{{ $userSale->total_orders }}</td>
                    <td class="text-right">{{ App\Libraries\AppLibrary::flatAmountFormat($userSale->total) }}</td>
                    <td class="text-right">{{ App\Libraries\AppLibrary::flatAmountFormat($userSale->total_tax) }}</td>
                    <td class="text-right">{{ App\Libraries\AppLibrary::flatAmountFormat($userSale->total + $userSale->total_tax) }}</td>
                </tr>
            @endforeach
            <tr class="total">
                <td colspan="2" class="text-center">{{ trans('all.label.total', [], 'en') }}</td>
                <td class="text-right">{{ $totalOrders }}</td>
                <td class="text-right">{{ App\Libraries\AppLibrary::flatAmountFormat($totalAmount) }}</td>
                <td class="text-right">{{ App\Libraries\AppLibrary::flatAmountFormat($totalTax) }}</td>
                <td class="text-right">{{ App\Libraries\AppLibrary::flatAmountFormat($totalWithTax) }}</td>
            </tr>
        </tbody>
    </table>
        </div>
        <div class="footer">
            @if($copyright)
                <p>{{ $copyright }}</p>
            @endif
        </div>
    </div>
</body>

</html>
