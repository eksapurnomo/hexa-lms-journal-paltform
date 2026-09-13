@extends('layouts.app')

@section('title', $app_setting['name'] . ' | '.__('Enrollment List'))

@section('content')
    <!-- ****Body-Section***** -->
    <div class="app-main-outer">
        <div class="app-main-inner">
            <div class="page-title-actions px-3 d-flex align-items-center mb-3">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }}</a></li>
                        <li class="breadcrumb-item active" aria-current="page">{{ __('Enrollment') }}</li>
                    </ol>
                </nav>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="card mb-5">
                        <div class="card-body">
                            <div class=" d-flex justify-content-end mb-3 align-items-center">
                                <form action="{{ route('enrollment.index') }}" method="GET" class="w-25 me-2">
                                    <div class="input-group">
                                        <input type="text" class="form-control" id="inputGroupFile04"
                                            aria-describedby="inputGroupFileAddon04" aria-label="Upload"
                                            placeholder="{{ __('Search') }}" name="cat_search" value="{{ request('cat_search') }}">
                                        <button class="btn btn-outline-primary px-3" type="submit"
                                            id="inputGroupFileAddon04"><i class="bi bi-search"></i></button>
                                    </div>
                                </form>
                                <div class="d-flex justify-content-end">
                                    <a href="{{ route('enrollment.index') }}" class="px-3">
                                        <i class="bi bi-arrow-counterclockwise"></i> {{ __('Reset') }}
                                    </a>
                                </div>
                            </div>
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th><strong>#</strong></th>
                                            <th><strong>{{ __('Enroll ID') }}</strong></th>
                                            <th><strong>{{ __('Thumbnail') }}</strong></th>
                                            <th><strong>{{ __('Student') }}</strong></th>
                                            <th><strong>{{ __('Course Title') }}</strong></th>
                                            <th style="width: 15%"><strong>{{ __('Progress') }}</strong></th>
                                            <th><strong>{{ __('Action') }}</strong></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($enrollments as $enrollment)
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td class="tableId">{{ $enrollment->id }}</td>
                                                <td>
                                                    <div class="d-flex align-items-center justify-content-start">
                                                        <img src="{{ $enrollment->user?->profilePicturePath }}"
                                                            alt="image" width="50" height="50"
                                                            style="border-radius: 16px; object-fit: cover">
                                                    </div>
                                                </td>
                                                <td class="tableProduct">{{ $enrollment->user?->name ?? 'N/A' }}</td>
                                                <td class="tableProduct">
                                                    <div class="product-pera">
                                                        <p class="priceDis">
                                                            @if (strlen($enrollment->course?->title) > 30)
                                                                {{ substr($enrollment->course?->title, 0, 30) . '...' }}
                                                            @else
                                                                {{ $enrollment->course?->title ?? 'N/A' }}
                                                            @endif
                                                        </p>
                                                    </div>
                                                </td>
                                                <td class="tableId">
                                                    {{ $enrollment->course_progress ?? 'N/A' }}
                                                    <div class="mb-3 progress">
                                                        <div class="progress-bar bg-danger progress-bar-animated progress-bar-striped"
                                                            role="progressbar"
                                                            aria-valuenow="{{ $enrollment->course_progress }}"
                                                            aria-valuemin="0" aria-valuemax="100"
                                                            style="width: {{ $enrollment->course_progress }}%;">
                                                            {{ $enrollment->course_progress }}%
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="tableAction">
                                                    <div class="action-icon">
                                                        @if ($enrollment->trashed())
                                                            <a class="circleIcon" class="circleIcon"
                                                                data-bs-toggle="tooltip" data-bs-placement="top"
                                                                data-bs-custom-class="custom-tooltip"
                                                                data-bs-title="{{ __('Restore Enrollment') }}"
                                                                href="{{ route('enrollment.restore', $enrollment->id) }}">
                                                                <img src="{{ asset('assets/images/icon/rotate-left.svg') }}"
                                                                    alt="icon">
                                                            </a>
                                                        @else
                                                            <button type="button" class="circleIcon" data-bs-toggle="modal"
                                                                data-bs-target="#enrollmentOverview{{ $enrollment->id }}">
                                                                <img src="{{ asset('assets/images/icon/eye.svg') }}"
                                                                    alt="icon">
                                                            </button>
                                                            <a class="circleIcon" class="circleIcon"
                                                                data-bs-toggle="tooltip" data-bs-placement="top"
                                                                data-bs-custom-class="custom-tooltip"
                                                                data-bs-title="{{ __('Suspend Enrollment') }}" href="#"
                                                                onclick="deleteAction('{{ route('enrollment.suspended', $enrollment->id) }}')">
                                                                <img src="{{ asset('assets/images/icon/ban.svg') }}"
                                                                    alt="icon">
                                                            </a>
                                                            <a class="circleIcon" class="circleIcon"
                                                                data-bs-toggle="tooltip" data-bs-placement="top"
                                                                data-bs-custom-class="custom-tooltip"
                                                                data-bs-title="{{ __('Delete Enrollment') }}" href="#"
                                                                onclick="deleteAction('{{ route('enrollment.destroy', $enrollment->id) }}')">
                                                                <img src="{{ asset('assets/images/icon/trash.svg') }}"
                                                                    alt="icon">
                                                            </a>
                                                        @endif
                                                    </div>
                                                </td>
                                            </tr>


                                            <!-- enrollment overview modal start -->
                                            <div class="modal fade" id="enrollmentOverview{{ $enrollment->id }}"
                                                tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                                                <div class="modal-dialog modal-dialog-centered">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h1 class="modal-title fs-5" id="exampleModalLabel">
                                                                {{ __('Enroll ID') }} #{{ $enrollment->id }}
                                                            </h1>
                                                            <button type="button" class="btn-close"
                                                                data-bs-dismiss="modal" aria-label="Close"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <div class="row">
                                                                <div class="col-12 py-1">
                                                                    <div class="d-flex align-items-center">
                                                                        <div class="me-3">
                                                                            <h5 class="mb-0">{{ __('Student') }}:</h5>
                                                                        </div>
                                                                        <div>
                                                                            <p
                                                                                class="mb-0 d-flex gap-2 align-items-center">
                                                                                <img src="{{ $enrollment->user?->profilePicturePath }}"
                                                                                    alt="image" width="30"
                                                                                    height="30"
                                                                                    style="border-radius: 50%; object-fit: cover">
                                                                                {{ $enrollment->user->name }}
                                                                            </p>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="col-12 py-1">
                                                                    <div class="d-flex align-items-center">
                                                                        <div class="me-3">
                                                                            <h5 class="mb-0">{{ __('Course Title') }}:</h5>
                                                                        </div>
                                                                        <div>
                                                                            <p class="mb-0">
                                                                                {{ $enrollment->course->title }}</p>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <div class="col-12 py-1">
                                                                <div class="d-flex align-items-center">
                                                                    <div class="me-3">
                                                                        <h5 class="mb-0">{{ __('Progress') }}:</h5>
                                                                    </div>
                                                                    <div>
                                                                        <p class="mb-0">
                                                                            {{ $enrollment->course_progress }}%</p>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="col-12 py-1">
                                                                <div class="d-flex align-items-center">
                                                                    <div class="me-3">
                                                                        <h5 class="mb-0">{{ __('Course Price') }}:</h5>
                                                                    </div>
                                                                    <div>
                                                                        <p class="mb-0">
                                                                            {{ currency($enrollment->course_price) }}
                                                                        </p>
                                                                    </div>
                                                                </div>
                                                            </div>


                                                            <div class="col-12 py-1">
                                                                <div class="d-flex align-items-center">
                                                                    <div class="me-3">
                                                                        <h5 class="mb-0">{{ __('Transaction Amount') }}:</h5>
                                                                    </div>
                                                                    <div>
                                                                        <p class="mb-0">
                                                                            {{ currency($enrollment?->transactions->first()?->payment_amount ?? 0.0) }}
                                                                        </p>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="col-12 py-1">
                                                                <div class="d-flex align-items-center">
                                                                    <div class="me-3">
                                                                        <h5 class="mb-0">{{ __('Last Activity') }}:</h5>
                                                                    </div>
                                                                    <div>
                                                                        <p class="mb-0">
                                                                            {{ $enrollment->last_activity ?? 'N/A' }}</p>
                                                                    </div>
                                                                </div>
                                                            </div>


                                                            <div class="col-12 py-1">
                                                                <div class="d-flex align-items-center">
                                                                    <div class="me-3">
                                                                        <h5 class="mb-0">{{ __('Status') }}:</h5>
                                                                    </div>
                                                                    <div>
                                                                        @if ($enrollment->trashed())
                                                                            <div class="statusItem">
                                                                                <div class="circleDot animatedPending">
                                                                                </div>
                                                                                <div class="statusText">
                                                                                    <span
                                                                                        class="stutsPanding">{{ __('Deleted') }}</span>
                                                                                </div>
                                                                            </div>
                                                                        @else
                                                                            <div class="statusItem">
                                                                                <div class="circleDot animatedCompleted">
                                                                                </div>
                                                                                <div class="statusText">
                                                                                    <span
                                                                                        class="stutsCompleted">{{ __('Active') }}</span>
                                                                                </div>
                                                                            </div>
                                                                        @endif
                                                                    </div>
                                                                </div>
                                                            </div>

                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <!-- enrollment overview modal end -->
                                        @empty
                                            <tr>
                                                <td colspan="9">
                                                    <h5 class="text-danger text-center m-0">{{ __('No Enrollment Available') }}</h5>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            {{ $enrollments->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ****End-Body-Section**** -->
    </div>
@endsection

