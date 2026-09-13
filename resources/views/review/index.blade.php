@extends('layouts.app')

@section('title', $app_setting['name'] . ' | '.__('Review List'))

@section('content')
    <!-- ****Body-Section***** -->
    <div class="app-main-outer">
        <div class="app-main-inner">
            <div class="page-title-actions px-3 d-flex">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }}</a></li>
                        <li class="breadcrumb-item active" aria-current="page">{{ __('Review') }}</li>
                    </ol>
                </nav>
            </div>
            <div class="row" id="deleteTableItem">
                <div class="col-md-12">
                    <div class="card mb-5">
                        <div class="card-body">
                            <div class=" d-flex justify-content-end mb-3 align-items-center">
                                <form action="{{ route('review.index') }}" method="GET" class="w-25 me-2">
                                    <div class="input-group">
                                        <input type="text" class="form-control" id="inputGroupFile04"
                                            aria-describedby="inputGroupFileAddon04" aria-label="Upload"
                                            placeholder="{{ __('Search') }}" name="cat_search" value="{{ request('cat_search') }}">
                                        <button class="btn btn-outline-primary px-3" type="submit"
                                            id="inputGroupFileAddon04"><i class="bi bi-search"></i></button>
                                    </div>
                                </form>
                                <div class="d-flex justify-content-end">
                                    <a href="{{ route('review.index') }}" class="px-3">
                                        <i class="bi bi-arrow-counterclockwise"></i> {{ __('Reset') }}
                                    </a>
                                </div>
                            </div>
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th><strong>#</strong></th>
                                            <th><strong>{{ __('Thumbnail') }}</strong></th>
                                            <th><strong>{{ __('Course Name') }}</strong></th>
                                            <th><strong>{{ __('Student') }}</strong></th>
                                            <th><strong>{{ __('Rating') }}</strong></th>
                                            <th><strong>{{ __('Comment') }}</strong></th>
                                            <th><strong>{{ __('Action') }}</strong></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($reviews as $review)
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>
                                                    <div class="d-flex align-items-center justify-content-start">
                                                        <img src="{{ $review->course?->mediaPath }}" alt="image"
                                                            width="80" height="80"
                                                            style="border-radius: 16px; object-fit: cover">
                                                    </div>
                                                </td>
                                                <td class="tableProduct">
                                                    <div class="product-pera">
                                                        <p class="priceDis">
                                                            @if (strlen($review->course?->title) > 30)
                                                                {{ substr($review->course?->title, 0, 30) . '...' }}
                                                            @else
                                                                {{ $review->course?->title ?? 'N/A' }}
                                                            @endif
                                                        </p>
                                                    </div>
                                                </td>
                                                <td class="tableId">{{ $review->user?->name }}</td>
                                                <td class="tableId">
                                                    @php
                                                        $rating = $review->rating;
                                                        $fullStars = floor($rating);
                                                        $hasHalfStar = $rating - $fullStars >= 0.5;
                                                    @endphp

                                                    @for ($i = 1; $i <= 5; $i++)
                                                        @if ($i <= $fullStars)
                                                            <i class="bi bi-star-fill bs-gold-star"></i>
                                                        @elseif ($hasHalfStar && $i == ceil($rating))
                                                            <i class="bi bi-star-half bs-gold-star"></i>
                                                        @else
                                                            <i class="bi bi-star bs-gold-star"></i>
                                                        @endif
                                                    @endfor
                                                </td>

                                                <td class="tableId">
                                                    @if (strlen($review->comment) > 50)
                                                        {{ substr($review->comment, 0, 50) . '...' }}
                                                    @else
                                                        {{ $review->comment }}
                                                    @endif
                                                </td>

                                                <td class="tableAction">
                                                    <div class="action-icon">
                                                        <a class="circleIcon" href="#" data-bs-toggle="tooltip"
                                                            data-bs-placement="top" data-bs-custom-class="custom-tooltip"
                                                            data-bs-title="{{ __('Delete Review') }}"
                                                            onclick="deleteAction('{{ route('review.destroy', $review->id) }}')">
                                                            <img src="{{ asset('assets/images/icon/trash.svg') }}"
                                                                alt="icon">
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6">
                                                    <h5 class="text-danger text-center m-0">{{ __('No data Available') }}</h5>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            {{ $reviews->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ****End-Body-Section**** -->
    </div>
@endsection
