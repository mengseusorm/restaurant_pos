<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Urbanist:ital,wght@0,100..900;1,100..900&display=swap"
        rel="stylesheet">
    <title>Stock report</title>
    <style>
        body {
            font-family: "Noto Sans SC", "Urbanist", "Microsoft YaHei", "Heiti SC", sans-serif;
            color: #1F1F39;
        }

        .container {
            width: 100%;
            height: 100vh;
            margin: auto;
            position: relative;
        }

        .report {
            width: 100%;
            text-align: center;
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
            border-radius: 8px;
            outline: 1px solid #EFF0F6;
            outline-offset: -1px;
            overflow: hidden;
            width: auto;
            border-collapse: collapse;
        }

        th,
        td {
            border: 1px solid #EFF0F6;
            padding: 8px 8px;
            text-align: left;
            font-size: 8;
            font-weight: 400;
        }

        th {
            background-color: #F8FBFB;
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
        }

        .footer {
            position: absolute;
            width: 100%;
            text-align: center;
            font-size: 12px;
            font-weight: 400;
            bottom: 20px;
        }
    </style>
</head>

<body>
    @php 
         $total_quantity = 0;
    @endphp 
    <div class="container">
        <div class="report">
            <p style="margin: 0px 0px 8px 0px;font-size: 16px;font-weight: bold">{{ App\Libraries\AppLibrary::textShortener($company['company_name'], 60) }}</p>
            <p>{{ App\Libraries\AppLibrary::textShortener($company['company_address'],60) }}</p>
            <p  style="color: #ff006b;margin: 0px 0px 8px 0px;font-size: 16px;font-weight: bold;">{{ trans('all.label.stock_report', [], 'en') }}</p>
            <table>
                <thead>
                    <tr>
                        <th>{{ trans('all.label.item_name') }}</th>
                        <th>{{ trans('all.label.stock_in', [], 'en') }}</th>
                        <th>{{ trans('all.label.stock_out', [], 'en') }}</th>
                        <th>{{ trans('all.label.remaining_stock', [], 'en') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($items as $item) 
                        <tr>
                            <td>{{ $item->item_name }}</td>
                            <td>{{ $item->stock_in }}</td>
                            <td>{{ $item->stock_out }}</td>
                            <td>{{ $item->remaining_stock }}</td>
                        </tr>
                    @endforeach 
                </tbody>
            </table>
        </div>
        <div class="footer">
            {{ $copyright }}
        </div>
    </div>
</body>

</html>