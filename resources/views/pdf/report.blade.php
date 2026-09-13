<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Styled Table</title>
    <style>
        @page {
            body {
                font-family: Arial, sans-serif;
                margin: 0;
                padding: 10px;
            }
        }

        .table-container {
            background: #ffffff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        thead {
            background-color: #eaf3ff;
        }

        thead th {
            font-weight: bold;
            text-align: left;
            padding: 10px;
        }

        tbody td {
            padding: 10px;
            text-align: left;
        }

        tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        tr:hover {
            background-color: #eaf3ff;
        }

        table,
        th,
        td {
            border: 1px solid #e0e0e0;
        }

        th,
        td {
            text-align: left;
            font-size: 14px;
        }
    </style>
</head>

<body>
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>{{ __('SL') }}</th>
                    <th>{{ __('Date') }}</th>
                    <th>{{ __('Course') }}</th>
                    <th>{{ __('Category') }}</th>
                    <th>{{ __('Price') }}</th>
                    <th>{{ __('Total Enroll') }}</th>
                    <th>{{ __('Total Transactions') }}</th>
                    <th>{{ __('Grand Total') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($reports as $report)
                    <tr>
                        <td class="tableId">{{ $loop->iteration }}</td>
                        <td class="tableId">{{ $report?->created_at }}</td>
                        <td class="tableId">{{ $report?->title }}</td>
                        <td class="tableId">{{ $report?->category?->title }}</td>
                        <td class="tableId">{{ $report?->price }}</td>
                        <td class="tableId">{{ $report->enrollments->count() }}</td>
                        <td class="tableId">{{ $report->transactions->count() }}</td>
                        <td class="tableId">
                            {{ $report->transactions->pluck('payment_amount')->toArray() ? array_sum($report->transactions->pluck('payment_amount')->toArray()) : 'N/A' }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center">{{ __('No data found') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</body>

</html>
