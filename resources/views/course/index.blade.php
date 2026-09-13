@extends('layouts.app')

@section('title', $app_setting['name'] . ' | '.__('Course List'))

@section('content')

    <!-- ****Body-Section***** -->
    <div class="app-main-outer">
        <div class="app-main-inner">
            <div
                class="page-title-actions px-3 py-3 d-flex justify-content-between align-items-center bg-white rounded mb-3">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb m-0 p-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }}</a></li>
                        <li class="breadcrumb-item active" aria-current="page">{{ __('Course') }}</li>
                    </ol>
                </nav>
                <div class="ms-auto">
                    <a href="{{ route('course.create') }}" class="btn btn-shadow btn-outline-primary mr-3 ms-auto">
                        +{{ __('New Course') }}
                    </a>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="card mb-5">
                        <div class="card-body">
                            <div class=" d-flex justify-content-end mb-3 align-items-center">
                                <form action="{{ route('course.index') }}" method="GET" class="w-25 me-2">
                                    <div class="input-group">
                                        <input type="text" class="form-control" id="inputGroupFile04"
                                            aria-describedby="inputGroupFileAddon04" aria-label="Upload"
                                            placeholder="{{ __('Search') }}" name="cat_search" value="{{ request('cat_search') }}">
                                        <button class="btn btn-outline-primary px-3" type="submit"
                                            id="inputGroupFileAddon04"><i class="bi bi-search"></i></button>
                                    </div>
                                </form>
                                <div class="d-flex justify-content-end">
                                    <a href="{{ route('course.index') }}" class="px-3">
                                        <i class="bi bi-arrow-counterclockwise"></i> {{ __('Reset') }}
                                    </a>
                                </div>
                            </div>
                            <div class="table-responsive-xl">
                                <table class="table table-responsive-xl">
                                    <thead>
                                        <tr>
                                            <th><strong>#</strong></th>
                                            <th><strong>{{ __('ID') }}</strong></th>
                                            <th><strong>{{ __('Free & Publish') }}</strong></th>
                                            <th><strong>{{ __('Course') }}</strong></th>
                                            <th><strong>{{ __('Price') }}</strong></th>
                                            <th><strong>{{ __('Instructor') }}</strong></th>
                                            <th><strong>{{ __('Status') }}</strong></th>
                                            <th><strong>{{ __('Action') }}</strong></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($courses as $course)
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>
                                                    {{ strtoupper($app_setting['name']) }}{{ $course->id }}</td>
                                                <td>
                                                    <div
                                                        class="d-flex
                                    justify-content-start gap-2">
                                                        <input data-bs-toggle="tooltip" data-bs-placement="top"
                                                            data-bs-custom-class="custom-tooltip"
                                                            data-bs-title="{{ $course->is_free ? __('Free') : __('Paid') }}"
                                                            href="#" class="form-check-input" type="radio"
                                                            name="exampleRadios{{ $course->id }}" id="courseFreeRadio"
                                                            onclick="sureAction('{{ route('course.free', $course->id) }}')"
                                                            {{ $course->is_free ? 'checked' : '' }}>
                                                        <span
                                                            class="badge {{ $course->is_free ? 'bg-success' : 'bg-dark' }}">{{ $course->is_free ? __('Free') : __('Paid') }}</span>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="listproduct-section">
                                                        <div class="courseImage">
                                                            <img src="{{ $course->mediaPath }}"
                                                                class="img-fluid w-100 h-100 object-fit-cover rounded-circle">
                                                        </div>
                                                        <div class="product-pera">
                                                            <p class="priceDis">
                                                                @if (strlen($course->title) > 20)
                                                                    {{ substr($course->title, 0, 20) . '...' }}
                                                                @else
                                                                    {{ $course->title }}
                                                                @endif
                                                            </p>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    @if ($app_setting['currency_position'] == 'Left')
                                                        {{ $app_setting['currency_symbol'] }}{{ $course->price ? $course->price : $course->regular_price }}
                                                    @else
                                                        {{ $course->price ? $course->price : $course->regular_price }}{{ $app_setting['currency_symbol'] }}
                                                    @endif
                                                </td>
                                                <td>{{ $course->instructor->user->name }}</td>
                                                <td>
                                                    @if ($course->trashed())
                                                        <div class="statusItem">
                                                            <div class="circleDot animatedPending"></div>
                                                            <div class="statusText">
                                                                <span class="stutsPanding">{{ __('Deleted') }}</span>
                                                            </div>
                                                        </div>
                                                    @else
                                                        <div class="statusItem">
                                                            <div class="circleDot animatedCompleted"></div>
                                                            <div class="statusText">
                                                                <span class="stutsCompleted">{{ __('Active') }}</span>
                                                            </div>
                                                        </div>
                                                    @endif
                                                </td>
                                                <td>
                                                    <div class="d-flex gap-3 flew-wrap">
                                                        @if ($course->trashed())
                                                            <a class="circleIcon" data-bs-toggle="tooltip"
                                                                data-bs-placement="top"
                                                                data-bs-custom-class="custom-tooltip"
                                                                data-bs-title="{{ __('Restore Course') }}"
                                                                href="{{ route('course.restore', $course->id) }}"><i
                                                                    class="bi bi-arrow-counterclockwise Circleicon"></i></a>
                                                        @else
                                                            <a class="circleIcon" data-bs-toggle="tooltip"
                                                                data-bs-placement="top"
                                                                data-bs-custom-class="custom-tooltip"
                                                                data-bs-title="{{ __('Course Overview') }}"
                                                                href="{{ route('course.show', $course->id) }}">
                                                                <img src="{{ asset('assets/images/icon/eye.svg') }}"
                                                                    alt="icon">
                                                            </a>
                                                            <a class="circleIcon" data-bs-toggle="tooltip"
                                                                data-bs-placement="top"
                                                                data-bs-custom-class="custom-tooltip"
                                                                data-bs-title="{{ __('Edit Course') }}"
                                                                href="{{ route('course.edit', $course->id) }}">
                                                                <img src="{{ asset('assets/images/icon/edit.svg') }}"
                                                                    alt="icon">
                                                            </a>
                                                            <a class="circleIcon" data-bs-toggle="tooltip"
                                                                data-bs-placement="top"
                                                                data-bs-custom-class="custom-tooltip"
                                                                data-bs-title="{{ __('Delete Course') }}" href="#"
                                                                onclick="deleteAction('{{ route('course.destroy', $course->id) }}')">
                                                                <img src="{{ asset('assets/images/icon/trash.svg') }}"
                                                                    alt="icon">
                                                            </a>
                                                        @endif
                                                    </div>
                                                </td>
                                            </tr>

                                        @empty
                                            <tr>
                                                <td colspan="8" class="text-center text-danger">{{ __('No data found') }}</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            {{ $courses->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ****End-Body-Section**** -->
    </div>
@endsection
