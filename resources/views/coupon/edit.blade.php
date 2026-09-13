@extends('layouts.app')

@section('title', $app_setting['name'] . ' | ' . __('Coupon Edit'))

@section('content')
    <!-- ****Body-Section***** -->
    <div class="app-main-outer">
        <div class="app-main-inner">
            <div class="page-title-actions px-3 d-flex">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }}</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('coupon.index') }}">{{ __('Coupon') }}</a></li>
                        <li class="breadcrumb-item active" aria-current="page">{{ __('Edit') }}</li>
                    </ol>
                </nav>
            </div>
            <div class="row" id="deleteTableItem">
                <div class="col-md-12">
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="m-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    <div class="main-card card d-flex h-100 flex-column">
                        <div class="card-body">
                            <h5 class="card-title py-2">{{ __('Update Coupon') }}</h5>
                            <form action="{{ route('coupon.update', $coupon->id) }}" method="POST"
                                enctype="multipart/form-data">
                                @csrf @method('PUT')
                                <div class="row">
                                    <div class="col-3">
                                        <div class="mb-3">
                                            <label class="form-label">{{ __('Coupon Code') }}</label>
                                            <input type="text" name="code" value="{{ $coupon->code }}" required
                                                class="form-control">
                                        </div>
                                    </div>

                                    <div class="col-3">
                                        <div class="mb-3">
                                            <label class="form-label">{{ __('Discount') }} ({{ __('In Amount') }})</label>
                                            <input type="number" step="any" min="1" name="discount"
                                                value="{{ $coupon->discount }}" required class="form-control">
                                        </div>
                                    </div>

                                    <div class="col-3">
                                        <div class="mb-3">
                                            <label class="form-label">{{ __('Applicable From') }}</label>
                                            <input type="date"
                                                value="{{ date('Y-m-d', strtotime($coupon->applicable_from)) }}"
                                                name="applicable_from" required class="form-control">
                                        </div>
                                    </div>

                                    <div class="col-3">
                                        <div class="mb-3">
                                            <label class="form-label">{{ __('Valid Unitl') }}</label>
                                            <input type="date"
                                                value="{{ date('Y-m-d', strtotime($coupon->valid_until)) }}"
                                                name="valid_until" required class="form-control">
                                        </div>
                                    </div>

                                    <div class="col-12">
                                        <div class="mb-3">
                                            <div class="form-check">
                                                <input name="is_active" class="form-check-input"
                                                    @if ($coupon->is_active) checked @endif type="checkbox">
                                                <label class="form-check-label">{{ __('Is Active') }}</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12 mt-3">
                                        <button type="submit" class="btn btn-primary btn-lg px-5">{{ __('Update') }}</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ****End-Body-Section**** -->
    </div>
@endsection
