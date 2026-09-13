@extends('layouts.app')

@section('title', $app_setting['name'] . ' | '.__('Select Quiz Course'))

@section('content')
    <!-- ****Body-Section***** -->
    <div class="app-main-outer">
        <div class="app-main-inner">
            <h3 class="mb-4 px-3 bg-primary-light text-dark p-4 rounded text-uppercase">{{ __('Select a course to view quizzes') }}</h3>
            <div class="row" id="deleteTableItem">
                <div class="col-md-12">
                    <div class="card mb-5">
                        <div class="card-body">
                            <div class=" d-flex justify-content-end mb-3 align-items-center">
                                <form action="{{ route('quiz.select_course') }}" method="GET" class="w-25 me-2">
                                    <div class="input-group">
                                        <input type="text" class="form-control" id="inputGroupFile04"
                                            aria-describedby="inputGroupFileAddon04" aria-label="Upload"
                                            placeholder="{{ __('Search') }}" name="cat_search" value="{{ request('cat_search') }}">
                                        <button class="btn btn-outline-primary px-3" type="submit"
                                            id="inputGroupFileAddon04"><i class="bi bi-search"></i></button>
                                    </div>
                                </form>
                                <div class="d-flex justify-content-end">
                                    <a href="{{ route('quiz.select_course') }}" class="px-3">
                                        <i class="bi bi-arrow-counterclockwise"></i> {{ __('Reset') }}
                                    </a>
                                </div>
                            </div>
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th><strong>{{ __('Thumbnail') }}</strong></th>
                                            <th><strong>{{ __('Course Name') }}</strong></th>
                                            <th><strong>{{ __('Category') }}</strong></th>
                                            <th><strong>{{ __('Price') }}</strong></th>
                                            <th><strong>{{ __('Action') }}</strong></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($courses as $course)
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center justify-content-start">
                                                        <img src="{{ $course?->mediaPath }}" alt="image" width="80"
                                                            height="80" style="border-radius: 16px; object-fit: cover">
                                                    </div>
                                                </td>
                                                <td class="tableProduct">
                                                    <div class="product-pera">
                                                        <a href="{{ route('course.edit', $course->id) }}"
                                                            class="priceDis">{{ $course?->title }}</a>
                                                    </div>
                                                </td>
                                                <td class="tableId">{{ $course->title }}</td>
                                                <td class="tableId">
                                                    @php
                                                        $price =
                                                            $course->price && $course->regular_price
                                                                ? $course->price
                                                                : $course->regular_price;
                                                    @endphp
                                                    @if ($app_setting['currency_position'] == 'Left')
                                                        {{ $app_setting['currency_symbol'] }}{{ $price }}
                                                    @else
                                                        {{ $price }}{{ $app_setting['currency_symbol'] }}
                                                    @endif
                                                </td>
                                                <td style="min-width: 120px;">
                                                    <a class="action-icon btn-outline-primary d-flex justify-content-center border border-primary p-2 rounded-3"
                                                        data-bs-toggle="tooltip" data-bs-placement="top"
                                                        data-bs-custom-class="custom-tooltip" data-bs-title="View Quizzes"
                                                        href="{{ route('quiz.index', $course->id) }}">
                                                        {{ __('View Quiz') }}
                                                    </a>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="7">
                                                    <h5 class="text-danger text-center m-0">{{ __('No Course Available') }}</h5>
                                                </td>
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
