@extends('layouts.app')

@section('title', $app_setting['name'] . ' | ' . __('Coupon List'))

@section('content')
    <!-- ****Body-Section***** -->
    <div class="app-main-outer">
        <div class="app-main-inner">
            <div
                class="page-title-actions px-3 py-3 d-flex justify-content-between align-items-center bg-white rounded mb-3">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb m-0 p-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }}</a></li>
                        <li class="breadcrumb-item active" aria-current="page">{{ __('Coupon') }}</li>
                    </ol>
                </nav>
                <div class="ms-auto">
                    <a href="{{ route('coupon.create') }}" class="btn btn-shadow btn-outline-primary mr-3 ms-auto">
                        +{{ __('New Coupon') }}
                    </a>
                </div>
            </div>

            <div class="row" id="deleteTableItem">
                <div class="col-md-12">
                    <div class="card mb-5">
                        <div class="card-body">
                            <div class=" d-flex justify-content-end mb-3 align-items-center">
                                <form action="{{ route('coupon.index') }}" method="GET" class="w-25 me-2">
                                    <div class="input-group">
                                        <input type="text" class="form-control" id="inputGroupFile04"
                                            aria-describedby="inputGroupFileAddon04" aria-label="Upload"
                                            placeholder="{{ __('Search') }}" name="cat_search" value="{{ request('cat_search') }}">
                                        <button class="btn btn-outline-primary px-3" type="submit"
                                            id="inputGroupFileAddon04"><i class="bi bi-search"></i></button>
                                    </div>
                                </form>
                                <div class="d-flex justify-content-end">
                                    <a href="{{ route('coupon.index') }}" class="px-3">
                                        <i class="bi bi-arrow-counterclockwise"></i> {{ __('Reset') }}
                                    </a>
                                </div>
                            </div>
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th><strong>#</strong></th>
                                            <th><strong>{{ __('Code') }}</strong></th>
                                            <th><strong>{{ __('Discount') }}</strong></th>
                                            <th><strong>{{ __('Is Active') }}</strong></th>
                                            @canany(['coupon.edit', 'coupon.update', 'coupon.destroy'])
                                                <th><strong>{{ __('Action') }}</strong></th>
                                            @endcanany
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($coupons as $coupon)
                                            <tr>
                                                <td class="tableId">{{ $loop->iteration }}</td>
                                                <td class="tableId">{{ $coupon->code }}</td>
                                                <td class="tableId">
                                                    @if ($app_setting['currency_position'] == 'Left')
                                                        {{ $app_setting['currency_symbol'] }}{{ $coupon->discount }}
                                                    @else
                                                        {{ $coupon->discount }}{{ $app_setting['currency_symbol'] }}
                                                    @endif
                                                </td>
                                                <td class="tableCustomar">
                                                    @if ($coupon->is_active)
                                                        <span class="badge rounded-pill text-bg-success">{{ __('Yes') }}</span>
                                                    @else
                                                        <span class="badge rounded-pill text-bg-danger">{{ __('No') }}</span>
                                                    @endif
                                                </td>
                                                @canany(['coupon.edit', 'coupon.update', 'coupon.destroy'])
                                                    <td class="tableAction">
                                                        <div class="action-icon">
                                                            @can(['coupon.edit', 'coupon.update'])
                                                                <a class="circleIcon" data-bs-toggle="tooltip"
                                                                    data-bs-placement="top" data-bs-custom-class="custom-tooltip"
                                                                    data-bs-title="{{ __('Edit Coupon') }}"
                                                                    href="{{ route('coupon.edit', $coupon->id) }}">
                                                                    <img src="{{ asset('assets/images/icon/edit.svg') }}"
                                                                        alt="icon">
                                                                </a>
                                                            @endcan
                                                            @can('coupon.destroy')
                                                                <a class="circleIcon" data-bs-toggle="tooltip"
                                                                    data-bs-placement="top" data-bs-custom-class="custom-tooltip"
                                                                    data-bs-title="{{ __('Delete Coupon') }}" href="#"
                                                                    onclick="deleteAction('{{ route('coupon.destroy', $coupon->id) }}')">
                                                                    <img src="{{ asset('assets/images/icon/trash.svg') }}"
                                                                        alt="icon">
                                                                </a>
                                                            @endcan
                                                        </div>
                                                    </td>
                                                @endcanany
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5">
                                                    <h5 class="text-danger text-center m-0">
                                                        {{ __('No Coupon Available') }}
                                                    </h5>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            {{ $coupons->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ****End-Body-Section**** -->
    </div>
@endsection
