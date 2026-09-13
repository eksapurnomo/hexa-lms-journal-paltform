@extends('layouts.app')

@section('title', $app_setting['name'] . ' | ' . __('Transaction List'))

@section('content')
    <!-- ****Body-Section***** -->
    <div class="app-main-outer">
        <div class="app-main-inner">
            <div class="page-title-actions px-3 d-flex">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }}</a></li>
                        <li class="breadcrumb-item active" aria-current="page">{{ __('Transaction') }}</li>
                    </ol>
                </nav>
            </div>
            <div class="row" id="deleteTableItem">
                <div class="col-md-12">
                    <div class="card mb-5">
                        <div class="card-body">
                            <button class="float-start btn btn-outline-primary px-4 py-2" type="submit"
                                onclick="printTable()"><i class="bi bi-printer-fill"></i> {{ __('Print') }}</button>
                            <div class=" d-flex justify-content-end mb-3 align-items-center">
                                <form action="{{ route('transaction.index') }}" method="GET" class="w-25 me-2">
                                    <div class="input-group">
                                        <input type="text" class="form-control" id="inputGroupFile04"
                                            aria-describedby="inputGroupFileAddon04" aria-label="Upload"
                                            placeholder="{{ __('Search') }}" name="cat_search"
                                            value="{{ request('cat_search') }}">
                                        <button class="btn btn-outline-primary px-3" type="submit"
                                            id="inputGroupFileAddon04"><i class="bi bi-search"></i></button>
                                    </div>
                                </form>
                                <div class="d-flex justify-content-end">
                                    <a href="{{ route('transaction.index') }}" class="px-3">
                                        <i class="bi bi-arrow-counterclockwise"></i> {{ __('Reset') }}
                                    </a>
                                </div>
                            </div>
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th><strong>#</strong></th>
                                            <th><strong>{{ __('Enrollment ID') }}</strong></th>
                                            <th><strong>{{ __('Course') }}</strong></th>
                                            <th><strong>{{ __('Student Phone') }}</strong></th>
                                            <th><strong>{{ __('Payment Amount') }}</strong></th>
                                            <th><strong>{{ __('Payment Method') }}</strong></th>
                                            <th><strong>{{ __('Payment Status') }}</strong></th>
                                            <th><strong>{{ __('Paid At') }}</strong></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($transactions as $transaction)
                                            <tr>
                                                <td class="tableId py-3">
                                                    {{ $loop->iteration }}
                                                </td>
                                                <td class="tableId py-3">
                                                    {{ $transaction->enrollment_id ? '#' . $transaction->enrollment_id : 'N/A' }}
                                                </td>
                                                <td class="tableId py-3">{{ $transaction->course_title }}</td>
                                                <td class="tableId py-3">{{ $transaction->user_phone }}</td>
                                                <td class="tableId py-3">
                                                    @if ($transaction->payable_amount != null)
                                                        @if ($app_setting['currency_position'] == 'Left')
                                                            {{ $app_setting['currency_symbol'] }}{{ $transaction->payable_amount }}
                                                        @else
                                                            {{ $transaction->payable_amount }}{{ $app_setting['currency_symbol'] }}
                                                        @endif
                                                    @else
                                                        N/A
                                                    @endif
                                                </td>
                                                <td class="tableId py-3">{{ $transaction->payment_method }}</td>
                                                <td class="tableStatus">
                                                    @if (!$transaction->is_paid)
                                                        <div class="statusItem">
                                                            <div class="circleDot animatedPending"></div>
                                                            <div class="statusText">
                                                                <span class="stutsPanding">{{ __('Unpaid') }}</span>
                                                            </div>
                                                        </div>
                                                    @else
                                                        <div class="statusItem">
                                                            <div class="circleDot animatedCompleted"></div>
                                                            <div class="statusText">
                                                                <span class="stutsCompleted">{{ __('Paid') }}</span>
                                                            </div>
                                                        </div>
                                                    @endif
                                                </td>
                                                </td>
                                                <td class="tableId py-3">{{ $transaction->paid_at ?? '-' }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="8" class="text-center">{{ __('No data found') }}</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            {{ $transactions->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ****End-Body-Section**** -->
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
@endpush
