@extends('layouts.app')
@section('title', $app_setting['name'] . ' | '.__('Payment Gateways'))

@section('content')
    <!-- ****Body-Section***** -->
    <div class="app-main-outer">
        <div class="app-main-inner">
            <div class="page-title-actions px-3 d-flex">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }}</a></li>
                        <li class="breadcrumb-item active" aria-current="page">{{ __('Payment Gateways') }}</li>
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

                    <div class="row">
                        <div class="col-4  mb-4">
                            <div class="main-card card d-flex h-100 flex-column">
                                <div class="card-body">
                                    <form action="{{ route('payment_gateway.update', $paypal->id) }}" method="POST"
                                        enctype="multipart/form-data">
                                        @csrf @method('PUT')
                                        <div class="row">
                                            <div class="col-6">
                                                <h5 class="py-2 h4">Paypal</h5>
                                            </div>
                                            <div class="col-6">
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input float-end"
                                                        @if ($paypal->is_active) checked @endif type="checkbox"
                                                        name="is_active">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="text-center mb-4">
                                            <img src="{{ $paypal->imagePath }}" class="mx-auto" alt="Paypal"
                                                width="50%">
                                        </div>

                                        <div class="row">
                                            <div class="col-6">
                                                <div class="form-group mb-3" data-select2-id="62">
                                                    <label class="form-label" for="categoryInput">{{ __('Mode') }}</label>
                                                    <select id="categoryInput" class="form-select form-control"
                                                        style="width: 100%;" name="mode" aria-hidden="true">
                                                        <option value="sandbox"
                                                            {{ $paypalConfig->mode === 'sandbox' ? 'selected' : '' }}>
                                                            {{ __('Sandbox') }}
                                                        </option>
                                                        <option value="live"
                                                            {{ $paypalConfig->mode === 'live' ? 'selected' : '' }}>{{ __('Live') }}
                                                        </option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <div class="mb-3">
                                                    <label class="form-label">App ID</label>
                                                    <input type="text" required name="app_id"
                                                        value="{{ $paypalConfig->app_id }}" class="form-control">
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <div class="mb-3">
                                                    <label class="form-label">Client ID</label>
                                                    <input type="text" required name="client_id"
                                                        value="{{ $paypalConfig->client_id }}" class="form-control">
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <div class="mb-3">
                                                    <label class="form-label">Client Secret</label>
                                                    <input type="text" required name="client_secret"
                                                        value="{{ $paypalConfig->client_secret }}" class="form-control">
                                                </div>
                                            </div>

                                            <div class="col-12">
                                                <button type="submit" class="btn btn-outline-primary px-5 mt-2">
                                                    {{ __('Update Paypal') }}
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <div class="col-4 mb-4">
                            <div class="main-card card d-flex h-100 flex-column">
                                <div class="card-body">
                                    <form action="{{ route('payment_gateway.update', $stripe->id) }}" method="POST"
                                        enctype="multipart/form-data">
                                        @csrf @method('PUT')
                                        <div class="row">
                                            <div class="col-6">
                                                <h5 class="py-2 h4">Stripe</h5>
                                            </div>
                                            <div class="col-6">
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input float-end"
                                                        @if ($stripe->is_active) checked @endif type="checkbox"
                                                        name="is_active">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="text-center mb-4">
                                            <img src="{{ $stripe->imagePath }}" class="mx-auto" alt="Stripe"
                                                width="50%">
                                        </div>
                                        <div class="row">
                                            <div class="col-12">
                                                <div class="mb-3">
                                                    <label class="form-label">Publishable Key</label>
                                                    <input type="text" required name="publishable_key"
                                                        value="{{ $stripeConfig->publishable_key }}" class="form-control">
                                                </div>
                                            </div>
                                            <div class="col-12">
                                                <div class="mb-3">
                                                    <label class="form-label">Secret Key</label>
                                                    <input type="text" required name="secret_key"
                                                        value="{{ $stripeConfig->secret_key }}" class="form-control">
                                                </div>
                                            </div>

                                            <div class="col-12">
                                                <button type="submit" class="btn btn-outline-primary px-5 mt-2">
                                                    {{ __('Update Stripe') }}
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <div class="col-4 mb-4">
                            <div class="main-card card d-flex h-100 flex-column">
                                <div class="card-body">
                                    <form action="{{ route('payment_gateway.update', $aamarpay->id) }}" method="POST"
                                        enctype="multipart/form-data">
                                        @csrf @method('PUT')
                                        <div class="row">
                                            <div class="col-6">
                                                <h5 class="py-2 h4">Aamarpay</h5>
                                            </div>
                                            <div class="col-6">
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input float-end"
                                                        @if ($aamarpay->is_active) checked @endif type="checkbox"
                                                        name="is_active">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="text-center mb-4">
                                            <img src="{{ $aamarpay->imagePath }}" class="mx-auto" alt="aamarpay"
                                                width="50%">
                                        </div>
                                        <div class="row">
                                            <div class="col-12">
                                                <div class="mb-3">
                                                    <label class="form-label">Store ID</label>
                                                    <input type="text" required name="store_id"
                                                        value="{{ $aamarpayConfig->store_id }}" class="form-control">
                                                </div>
                                            </div>
                                            <div class="col-12">
                                                <div class="mb-3">
                                                    <label class="form-label">Signature Key</label>
                                                    <input type="text" required name="signature_key"
                                                        value="{{ $aamarpayConfig->signature_key }}"
                                                        class="form-control">
                                                </div>
                                            </div>

                                            <div class="col-12">
                                                <button type="submit" class="btn btn-outline-primary px-5 mt-2">
                                                    {{ __('Update Aamarpay') }}
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <div class="col-4 mb-4">
                            <div class="main-card card d-flex h-100 flex-column">
                                <div class="card-body">
                                    <form action="{{ route('payment_gateway.update', $razorpay->id) }}" method="POST"
                                        enctype="multipart/form-data">
                                        @csrf @method('PUT')
                                        <div class="row">
                                            <div class="col-6">
                                                <h5 class="py-2 h4">Razorpay</h5>
                                            </div>
                                            <div class="col-6">
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input float-end"
                                                        @if ($razorpay->is_active) checked @endif type="checkbox"
                                                        name="is_active">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="text-center mb-4">
                                            <img src="{{ $razorpay->imagePath }}" class="mx-auto" alt=""
                                                width="50%">
                                        </div>
                                        <div class="row">
                                            <div class="col-12">
                                                <div class="mb-3">
                                                    <label class="form-label">Key</label>
                                                    <input type="text" required name="key"
                                                        value="{{ $razorpayConfig->key }}" class="form-control">
                                                </div>
                                            </div>
                                            <div class="col-12">
                                                <div class="mb-3">
                                                    <label class="form-label">Secret key</label>
                                                    <input type="text" required name="secret"
                                                        value="{{ $razorpayConfig->secret }}" class="form-control">
                                                </div>
                                            </div>

                                            <div class="col-12">
                                                <button type="submit" class="btn btn-outline-primary px-5 mt-2">
                                                    {{ __('Update Razorpay') }}
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <div class="col-4 mb-4">
                            <div class="main-card card d-flex h-100 flex-column">
                                <div class="card-body">
                                    <form action="{{ route('payment_gateway.update', $twoCheckout->id) }}" method="POST"
                                        enctype="multipart/form-data">
                                        @csrf @method('PUT')
                                        <div class="row">
                                            <div class="col-6">
                                                <h5 class="py-2 h4">2Checkout</h5>
                                            </div>
                                            <div class="col-6">
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input float-end"
                                                        @if ($twoCheckout->is_active) checked @endif type="checkbox"
                                                        name="is_active">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="text-center mb-4">
                                            <img src="{{ $twoCheckout->imagePath }}" class="mx-auto w-25"
                                                alt="2Checkout" width="50%">
                                        </div>

                                        <div class="row">
                                            <div class="col-12">
                                                <div class="mb-3">
                                                    <label class="form-label">Merchant</label>
                                                    <input type="text" required name="merchant"
                                                        value="{{ $twoCheckoutConfig->merchant }}" class="form-control">
                                                </div>
                                            </div>

                                            <div class="col-12">
                                                <button type="submit" class="btn btn-outline-primary px-5 mt-2">
                                                    {{ __('Update 2Checkout') }}
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ****End-Body-Section**** -->
    </div>
@endsection
