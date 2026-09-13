@extends('layouts.app')

@section('title', $app_setting['name'] . ' | ' . __('Pre-defined Notification List'))

@push('styles')
    <style>
        .switch {
            margin-right: .75rem;
            position: relative;
            vertical-align: middle;
            margin-bottom: 0;
            display: inline-block;
            border-radius: 30rem;
            cursor: pointer;
            min-height: 1.35rem;
            font-size: .9375rem;
            line-height: 1.4;
        }

        .switch-input {
            opacity: 0;
            position: absolute;
            padding: 0;
            margin: 0;
            z-index: -1;
        }

        .switch-primary.switch .switch-input:checked~.switch-toggle-slider {
            background: #7367f0;
            color: #fff;
            box-shadow: 0 2px 6px 0 rgba(115, 103, 240, .3);
        }

        .switch-input:checked~.switch-toggle-slider {
            background: #7367f0;
            color: #fff;
            box-shadow: 0 2px 6px 0 rgba(115, 103, 240, .3);
        }

        .switch .switch-toggle-slider {
            width: 2.8rem;
            height: 1.35rem;
            font-size: .625rem;
            line-height: 1.35rem;
            border: 1px solid rgba(0, 0, 0, 0);
            top: 50%;
            transform: translateY(-50%);
        }

        .switch-toggle-slider {
            position: absolute;
            overflow: hidden;
            border-radius: 30rem;
            background: #eaeaec;
            color: rgba(47, 43, 61, .4);
            transition-duration: .2s;
            transition-property: left, right, background, box-shadow;
            cursor: pointer;
            user-select: none;
            box-shadow: 0 0 .25rem 0 rgba(0, 0, 0, .16) inset;
        }

        .switch-input:checked~.switch-toggle-slider .switch-on {
            left: 0;
        }

        .switch .switch-on {
            padding-left: .25rem;
            padding-right: 1.2rem;
        }

        .switch-on {
            left: -100%;
        }

        .switch-off,
        .switch-on {
            height: 100%;
            width: 100%;
            text-align: center;
            position: absolute;
            top: 0;
            transition-duration: .2s;
            transition-property: left, right;
        }

        .switch .switch-toggle-slider i {
            position: relative;
            font-size: .9375rem;
            top: -1.35px;
        }

        .switch-input:checked~.switch-toggle-slider .switch-off {
            left: 100%;
            color: rgba(0, 0, 0, 0);
        }

        .switch .switch-off {
            padding-left: 1.1rem;
            padding-right: .25rem;
        }

        .switch-off {
            left: 0;
        }

        .switch-off,
        .switch-on {
            height: 100%;
            width: 100%;
            text-align: center;
            position: absolute;
            top: 0;
            transition-duration: .2s;
            transition-property: left, right;
        }

        .switch .switch-input~.switch-label {
            padding-left: 3rem;
        }

        .switch .switch-label {
            top: .01875rem;
        }

        .switch-label {
            display: inline-block;
            font-weight: 400;
            color: #444050;
            position: relative;
            cursor: default;
        }

        .switch .switch-input:checked~.switch-toggle-slider::after {
            left: 1.25rem;
        }

        .switch .switch-toggle-slider::after {
            margin-left: 5px;
            width: 14px;
            height: 14px;
        }

        .switch-toggle-slider::after {
            content: "";
            position: absolute;
            left: 0;
            display: block;
            border-radius: 999px;
            background: #fff;
            box-shadow: 0 .0625rem .375rem 0 rgba(47, 43, 61, .1);
            transition-duration: .2s;
            transition-property: left, right, background;
        }

        .switch-toggle-slider::after {
            top: 50%;
            transform: translateY(-50%);
        }
    </style>
@endpush

@section('content')
    <!-- ****Body-Section***** -->
    <div class="app-main-outer">
        <div class="app-main-inner">
            <div class="page-title-actions px-3 d-flex">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }}</a></li>
                        <li class="breadcrumb-item active" aria-current="page">{{ __('Notification') }}</li>
                    </ol>
                </nav>
                {{-- <div class="ms-auto mb-3">
                    <a href="{{ route('notification.create') }}" class="btn-shadow mr-3 btn btn-dark ms-auto">
                        + New Notification
                    </a>
                </div> --}}
            </div>

            <div class="row">
                <div class="col-lg-12">
                    <div class="card mb-3">
                        <div class="card-body">
                            <h3 class="m-0 p-0">{{ __('Pre-defined Notification List') }}</h3>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row" id="deleteTableItem">
                <div class="col-md-12">
                    <div class="card mb-5">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th><strong>#</strong></th>
                                            <th><strong>{{ __('Title') }}</strong></th>
                                            <th><strong>{{ __('Is Enabled') }}</strong></th>
                                            <th><strong>{{ __('Action') }}</strong></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($notifications as $notification)
                                            <tr>
                                                <td class="tableId">{{ $loop->iteration }}</td>
                                                <td class="tableId">{{ filterNotificationType($notification->type) }}</td>
                                                <td class="tableCustomar">
                                                    <label class="switch switch-primary">
                                                        <a href="{{ route('notification.switch.status', $notification->id) }}"
                                                            class="confirmAlert">
                                                            <input type="checkbox" class="switch-input"
                                                                @if ($notification->is_enabled == true) checked @endif>
                                                            <span class="switch-toggle-slider">
                                                                <span class="switch-on">
                                                                    <svg class="svg-inline--fa fa-check" aria-hidden="true"
                                                                        focusable="false" data-prefix="fas"
                                                                        data-icon="check" role="img"
                                                                        xmlns="http://www.w3.org/2000/svg"
                                                                        viewBox="0 0 512 512" data-fa-i2svg="">
                                                                        <path fill="currentColor"
                                                                            d="M470.6 105.4c12.5 12.5 12.5 32.8 0 45.3l-256 256c-12.5 12.5-32.8 12.5-45.3 0l-128-128c-12.5-12.5-12.5-32.8 0-45.3s32.8-12.5 45.3 0L192 338.7 425.4 105.4c12.5-12.5 32.8-12.5 45.3 0z">
                                                                        </path>
                                                                    </svg><!-- <i class="fas fa-check"></i> Font Awesome fontawesome.com -->
                                                                </span>
                                                                <span class="switch-off">
                                                                    <svg class="svg-inline--fa fa-xmark" aria-hidden="true"
                                                                        focusable="false" data-prefix="fas"
                                                                        data-icon="xmark" role="img"
                                                                        xmlns="http://www.w3.org/2000/svg"
                                                                        viewBox="0 0 320 512" data-fa-i2svg="">
                                                                        <path fill="currentColor"
                                                                            d="M310.6 150.6c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0L160 210.7 54.6 105.4c-12.5-12.5-32.8-12.5-45.3 0s-12.5 32.8 0 45.3L114.7 256 9.4 361.4c-12.5 12.5-12.5 32.8 0 45.3s32.8 12.5 45.3 0L160 301.3 265.4 406.6c12.5 12.5 32.8 12.5 45.3 0s12.5-32.8 0-45.3L205.3 256 310.6 150.6z">
                                                                        </path>
                                                                    </svg>
                                                                </span>
                                                            </span>
                                                        </a>
                                                    </label>
                                                </td>

                                                <td class="tableAction">
                                                    <div class="action-icon">
                                                        <a data-bs-toggle="tooltip" data-bs-placement="top"
                                                            data-bs-custom-class="custom-tooltip"
                                                            data-bs-title="{{ __('Edit Notification') }}"
                                                            href="{{ route('notification.edit', $notification->id) }}"><i
                                                                class="bi bi-pen Circleicon"></i></a>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
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
        $(document).ready(function() {
            $('.confirmAlert').on('click', function(e) {
                e.preventDefault();
                var href = $(this).attr('href');
                Swal.fire({
                    title: "{{ __('Are you sure?') }}",
                    text: "{{ __('You wont be able to revert this!') }}",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: "{{ __('Yes, Confirm it!') }}"
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = href;
                    }
                })
            })
        })
    </script>
@endpush
