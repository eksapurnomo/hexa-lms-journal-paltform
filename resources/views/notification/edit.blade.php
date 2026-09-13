@extends('layouts.app')

@section('title', $app_setting['name'] . ' | '.__('Notification Edit'))

@section('content')
    <!-- ****Body-Section***** -->
    <div class="app-main-outer">
        <div class="app-main-inner">
            <div class="page-title-actions px-3 d-flex">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }}</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('notification.index') }}">{{ __('Notification') }}</a></li>
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
                            <h5 class="card-title py-2">{{ __('Edit Notification') }}</h5>

                            <ul class="mb-3">
                                <li>{{ __('Use') }} <span class="fw-bold bg-light mx-1">{course_title}</span>
                                    {{ __('to replace course title') }}
                                </li>
                                <li>{{ __('Use') }} <span class="fw-bold bg-light mx-1">{user_name}</span> {{ __('to replace user name') }}</li>
                            </ul>

                            <form action="{{ route('notification.update', $notification->id) }}" method="POST"
                                enctype="multipart/form-data">
                                @csrf @method('PUT')
                                <div class="row">
                                    <div class="col-4">
                                        <div class="mb-3">
                                            <label for="contentInput" class="form-label">{{ __('Content') }}</label>
                                            <textarea required name="content" class="form-control" id="contentInput">{{ $notification->content }}</textarea>
                                        </div>
                                    </div>

                                    <div class="col-12">
                                        <div class="mb-3">
                                            <div class="form-check">
                                                <input id="enabledInput" name="is_enabled" class="form-check-input"
                                                    type="checkbox" @if ($notification->is_enabled) checked @endif>
                                                <label for="enabledInput" class="form-check-label">{{ __('Is Enabled') }}</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <button type="submit" class="btn bgBlue btn-dipBlue">{{ __('Update') }}</button>
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
