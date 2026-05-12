<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Items Report</title>
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
        $total_quantity = 0;
        $total_amount = 0;
        $total_vat = 0;
        $total_amount_vat = 0;
    @endphp
    <div class="container">
        <div class="report">
            <p style="margin: 0px 0px 8px 0px;font-size: 16px;font-weight: bold">{{ App\Libraries\AppLibrary::textShortener($company['company_name'], 60) }}</p>
            <p>{{ App\Libraries\AppLibrary::textShortener($company['company_address'], 60) }}</p>
            <p style="color: #ff006b;margin: 0px 0px 8px 0px;font-size: 16px;font-weight: bold;">{{ trans('all.label.items_report', [], 'en') }}</p>
            <p style="margin: 0px 0px 16px 0px;font-size: 12px;">
                <strong>From:</strong> {{ $fromDate }}
                <strong style="margin-left: 20px;">To:</strong> {{ $toDate }}
            </p>
            <table>
                <thead>
                    <tr>
                        <th>{{ trans('all.label.name', [], 'en') }}</th>
                        <th>{{ trans('all.label.item_category_id', [], 'en') }}</th>
                        <th>{{ trans('all.label.quantity', [], 'en') }}</th>
                        <th>{{ trans('all.label.amount', [], 'en') }}</th>
                        <th>{{ trans('all.label.vat', [], 'en') }}</th>
                        <th>{{ trans('all.label.amount_vat', [], 'en') }}</th>
                        <th>{{ trans('all.label.currency', [], 'en') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($items as $item)
                        @php
                            $quantity = $item->total_ordered_qty ?? 0;
                            $amount = $item->current_total_price ?? 0;
                            $vat = $item->total_tax ?? 0;
                            $amountVat = $amount + $vat;

                            $total_quantity += $quantity;
                            $total_amount += $amount;
                            $total_vat += $vat;
                            $total_amount_vat += $amountVat;
                        @endphp
                        <tr>
                            <td>{{ $item->name }}</td>
                            <td>{{ $item->category_name }}</td>
                            <td>{{ $quantity }}</td>
                            <td>{{ App\Libraries\AppLibrary::flatAmountFormat($amount) }}</td>
                            <td>{{ App\Libraries\AppLibrary::flatAmountFormat($vat) }}</td>
                            <td>{{ App\Libraries\AppLibrary::flatAmountFormat($amountVat) }}</td>
                            <td>{{ $item->order_currency }}</td>
                        </tr>
                    @endforeach
                    <tr class="total">
                        <td>{{ trans('all.label.total', [], 'en') }}</td>
                        <td></td>
                        <td>{{ $total_quantity }}</td>
                        <td>{{ App\Libraries\AppLibrary::flatAmountFormat($total_amount) }}</td>
                        <td>{{ App\Libraries\AppLibrary::flatAmountFormat($total_vat) }}</td>
                        <td>{{ App\Libraries\AppLibrary::flatAmountFormat($total_amount_vat) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="footer">
            {{ $copyright }}
        </div>
    </div>
</body>

</html>
