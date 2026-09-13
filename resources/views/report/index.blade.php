@extends('layouts.app')

@section('title', $app_setting['name'] . ' | ' .__('Report'))

@section('content')

    <div class="app-main-outer">
        <div class="app-main-inner">
            {{-- top --}}

            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-lg-6 col-xl-5">

                                    <select name="filter_type"
                                        class="form-select js-states form-control rounded-3 text-uppercase"
                                        id="useSelectric">
                                        <option value="all" class="py-4 text-uppercase"
                                            {{ request('filter_type') == 'all' ? 'selected' : '' }}>{{ __('all') }}</option>
                                        <option value="sale" class="py-4 text-uppercase"
                                            {{ request('filter_type') == 'sale' ? 'selected' : '' }}>{{ __('sale') }}</option>
                                        <option value="unpaid" class="py-4 text-uppercase"
                                            {{ request('filter_type') == 'unpaid' ? 'selected' : '' }}>
                                            {{ __('unpaid') }}
                                        </option>
                                    </select>

                                </div>
                                <div class="col-lg-6 col-xl-3 mt-3 mt-lg-0 mt-xl-0">
                                    @if (request('filter_type') != 'unpaid')
                                        <div class="date-controller">
                                            <input class="form-control" type="text" name="daterange" autocomplete="off">
                                            <img src="{{ asset('assets/images/menu/calendar.svg') }}" alt="icon"
                                                class="date-icon">
                                        </div>
                                    @endif
                                </div>
                                <div
                                    class="col-lg-12 col-xl-4 mt-3 mt-xl-0 d-flex justify-content-end align-items-center gap-3">
                                    <button class="btn btn-outline-primary btn-lg" type="submit" onclick="printTable()"><i
                                            class="bi bi-printer-fill"></i> {{ __('Print') }}</button>
                                    <div class="dropdown">
                                        <button class="btn btn-outline-primary btn-lg dropdown-toggle" type="submit"
                                            data-bs-toggle="dropdown">
                                            {{ __('Export As') }}
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-dark">
                                            <li>
                                                <a class="dropdown-item" id="exportCSV" href="{{ route('report.exportCSV') }}">
                                                    {{ __('CSV File') }}
                                                </a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item" href="{{ route('report.generate.pdf') }}" target="_blank">
                                                    {{ __('PDF File') }}
                                                </a>
                                            </li>
                                            <form id="filterForm" action="{{ route('report.generate.pdf') }}" method="GET"
                                                target="_blank">
                                                <input type="hidden" name="filter_type" id="filterType" value="">
                                                <input type="hidden" name="daterange" id="filterDate" value="">
                                                <input type="hidden" name="page" value="{{ request('page') ?? 1 }}" />
                                            </form>
                                            <form id="filterFormCSV" action="{{ route('report.exportCSV') }}" method="GET"
                                                target="_blank">
                                                <input type="hidden" name="filter_type" id="filterTypeCSV" value="">
                                                <input type="hidden" name="daterange" id="filterDateCSV" value="">
                                                <input type="hidden" name="page" value="{{ request('page') ?? 1 }}" />
                                            </form>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row" id="deleteTableItem">
                <div class="col-md-12">
                    <div class="card mb-5">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-responsive-xl">
                                    <thead>
                                        <tr>
                                            <th><strong>{{ __('SL') }}</strong></th>
                                            <th><strong>{{ __('Create Date') }}</strong></th>
                                            <th><strong>{{ __('Course') }}</strong></th>
                                            <th><strong>{{ __('Category') }}</strong></th>
                                            <th><strong>{{ __('Price') }}</strong></th>
                                            <th><strong>{{ __('Total Enroll') }}</strong></th>
                                            <th><strong>{{ __('Total Transactions') }}</strong></th>
                                            <th><strong>{{ __('Grand Total') }}</strong></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($reports as $report)
                                            <tr>
                                                <td class="tableId">{{ $reports->firstItem() + $loop->index }}</td>
                                                <td class="tableId">
                                                    {{ Carbon\Carbon::parse($report->created_at)->format('d,M Y') }}</td>
                                                <td class="tableId">{{ $report?->title }}</td>
                                                <td class="tableId">{{ $report?->category?->title }}</td>
                                                <td class="tableId">
                                                    {{ $report?->price && $report?->regular_price ? currency($report?->price) : currency($report?->regular_price) }}
                                                </td>
                                                <td class="tableId">{{ $report->enrollments->count() }}</td>
                                                <td class="tableId">{{ $report->transactions->count() }}</td>
                                                <td class="tableId">
                                                    {{ $report->transactions->pluck('payment_amount')->toArray() ? currency(array_sum($report->transactions->pluck('payment_amount')->toArray())) : 'N/A' }}
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="8" class="text-center text-danger fw-bold">
                                                    {{ __('No information available') }}.
                                                </td>
                                            </tr>
                                        @endforelse

                                    </tbody>
                                </table>
                            </div>
                            <div class="mt-3">
                                {{ $reports->links() }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- bottom --}}
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function printTable() {
            const table = document.querySelector('#deleteTableItem .table-responsive');
            const printWindow = window.open('', '', 'width=800,height=600');
            printWindow.document.write(`
            <html>
                <head>
                    <title>Print Table</title>
                    <style>
                        /* Custom styles for the printed table */
                        body {
                            font-family: Arial, sans-serif;
                            margin: 20px;
                        }
                        table {
                            width: 100%;
                            border-collapse: collapse;
                        }
                        table, th, td {
                            border: 1px solid black;
                        }
                        th, td {
                            padding: 10px;
                            text-align: left;
                        }
                        th {
                            background-color: #f4f4f4;
                        }
                    </style>
                </head>
                <body>
                    ${table.outerHTML}
                </body>
            </html>
        `);
            printWindow.document.close();
            printWindow.print();
            printWindow.onafterprint = function() {
                printWindow.close();
            };
        }
    </script>


    <script>
        $(function() {
            let dateRange = $('input[name="daterange"]');
            let filterType = $('select[name="filter_type"]');

            function cb(start, end) {
                dateRange.val(start.format('YYYY-MM-DD') + '_' + end.format('YYYY-MM-DD'));
            }


            const selectedStartedDate = "{{ request()->input('daterange') }}";

            let start, end;

            if (selectedStartedDate) {
                start = moment(selectedStartedDate.split('_')[0]);
                end = moment(selectedStartedDate.split('_')[1]);
            } else {
                start = moment().subtract(29, 'days');
                end = moment();
            }

            dateRange.daterangepicker({
                startDate: start,
                endDate: end,
                opens: 'left',
                ranges: {
                    'Today': [moment(), moment()],
                    'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                    'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                    'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                    'This Month': [moment().startOf('month'), moment().endOf('month')],
                    'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1,
                        'month').endOf('month')]
                }
            }, cb);

            $('select[name="filter_type"]').on('change', function() {
                setUrl();
            });

            dateRange.change(() => {
                setUrl();
            });

            function setUrl() {
                let startDate = dateRange.val().split(' - ')[0];
                let endDate = dateRange.val().split(' - ')[1];

                startDate = moment(startDate).format('YYYY-MM-DD');
                endDate = moment(endDate).format('YYYY-MM-DD')

                window.location.href = "{{ route('report.filter') }}?filter_type=" + filterType.val() +
                    "&daterange=" +
                    startDate + "_" + endDate;
            }
        });
    </script>

    <script>
        document.querySelector('.dropdown-item[href="{{ route('report.generate.pdf') }}"]').addEventListener('click',
            function(e) {
                e.preventDefault();

                // Capture the filter values
                const filterType = document.querySelector('select[name="filter_type"').value;
                const dateRange = document.querySelector('input[name="daterange"]').value;

                document.getElementById('filterType').value = filterType;
                document.getElementById('filterDate').value = dateRange;

                document.getElementById('filterForm').submit();
            });

        document.querySelector('.dropdown-item[href="{{ route('report.exportCSV') }}"]').addEventListener('click',
            function(e) {
                e.preventDefault();

                // Capture the filter values
                const filterType = document.querySelector('select[name="filter_type"]').value;
                const dateRange = document.querySelector('input[name="daterange"]').value;

                document.getElementById('filterTypeCSV').value = filterType;
                document.getElementById('filterDateCSV').value = dateRange;

                document.getElementById('filterFormCSV').submit();
            });
    </script>
@endpush
