@extends('layouts.app')

@section('title', $app_setting['name'] . ' | '.__('Testimonial List'))

@section('content')

    <div class="app-main-outer">
        <div class="app-main-inner">
            {{-- top --}}
            <div class="page-title-actions px-3 d-flex">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }}</a></li>
                        <li class="breadcrumb-item active" aria-current="page">{{ __('Testimonials') }}</li>
                    </ol>
                </nav>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body d-flex align-items-center justify-content-between">
                            <h3 class="m-0 p-0">
                                {{ __('Testimonial List') }}</h3>
                            <a href="{{ route('testimonial.create') }}" class="btn btn-outline-primary">
                                {{ __('+ Add New Testimonial') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            {{-- <div class="row">
                <div class="col-12 mt-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th scope="col">#</th>
                                            <th scope="col">Image</th>
                                            <th scope="col">Name</th>
                                            <th scope="col">Designation</th>
                                            <th scope="col">Status</th>
                                            <th scope="col">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($testimonials as $key => $testimonial)
                                            <tr>
                                                <th scope="row">{{ $key + 1 }}</th>
                                                <td>
                                                    <img src="{{ $testimonial->mediaPath }}" width="100"
                                                        height="100" />
                                                </td>
                                                <td>{{ $testimonial->name }}</td>
                                                <td>{{ $testimonial->designation }}</td>
                                                <td>
                                                    @if ($testimonial->status == 1)
                                                        <span class="badge badge-success">Active</span>
                                                    @else
                                                        <span class="badge badge-danger">Inactive</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <a href="#" class="btn btn-primary">Edit</a>
                                                </td>
                                            </tr>

                                        @empty
                                            <tr>
                                                <td colspan="7">
                                                    <h5 class="text-danger text-center m-0">no content available</h5>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div> --}}

            <div class="row" id="deleteTableItem">
                <div class="col-md-12">
                    <div class="card mb-5">
                        <div class="card-body">
                            <div class=" d-flex justify-content-end mb-3 align-items-center">
                                <form action="{{ route('testimonial.index') }}" method="GET" class="w-25 me-2">
                                    <div class="input-group">
                                        <input type="text" class="form-control" id="inputGroupFile04"
                                            aria-describedby="inputGroupFileAddon04" aria-label="Upload"
                                            placeholder="{{ __('Search') }}" name="cat_search" value="{{ request('cat_search') }}">
                                        <button class="btn btn-outline-primary px-3" type="submit"
                                            id="inputGroupFileAddon04"><i class="bi bi-search"></i></button>
                                    </div>
                                </form>
                                <div class="d-flex justify-content-end">
                                    <a href="{{ route('testimonial.index') }}" class="px-3">
                                        <i class="bi bi-arrow-counterclockwise"></i> {{ __('Reset') }}
                                    </a>
                                </div>
                            </div>
                            <div class="table-responsive-lg">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th><strong>#</strong></th>
                                            <th><strong>{{ __('Image') }}</strong></th>
                                            <th><strong>{{ __('Name') }}</strong></th>
                                            <th><strong>{{ __('Degisnation') }}</strong></th>
                                            <th><strong>{{ __('Review') }}</strong></th>
                                            <th><strong>{{ __('Status') }}</strong></th>
                                            <th><strong>{{ __('Action') }}</strong></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($testimonials as $testimonial)
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>
                                                    <div class="d-flex align-items-center justify-content-start">
                                                        <img src="{{ $testimonial->mediaPath }}" alt="image"
                                                            width="80" height="80"
                                                            style="border-radius: 16px; object-fit: cover">
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="product-pera">
                                                        <p class="priceDis">{{ ucwords($testimonial->name) }}</p>
                                                    </div>
                                                </td>

                                                <td>
                                                    {{ ucwords($testimonial->designation) }}
                                                </td>

                                                <td>
                                                    @php
                                                        $rating = $testimonial->rating;
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

                                                <td>
                                                    @if ($testimonial->is_active == 0)
                                                        <div
                                                            class="statusItem d-flex justify-content-start align-items-center">
                                                            <div class="circleDot animatedPending"></div>
                                                            <div class="statusText">
                                                                <span class="stutsPanding">{{ __('Pending') }}</span>
                                                            </div>
                                                        </div>
                                                    @else
                                                        <div
                                                            class="statusItem d-flex justify-content-center align-items-center">
                                                            <div class="circleDot animatedCompleted"></div>
                                                            <div class="statusText">
                                                                <span class="stutsCompleted">{{ __('Active') }}</span>
                                                            </div>
                                                        </div>
                                                    @endif
                                                </td>
                                                <td>
                                                    <div
                                                        class="d-flex justify-content-start align-items-center gap-3 flex-wrap">
                                                        @if ($testimonial->trashed())
                                                            <a class="circleIcon" data-bs-toggle="tooltip"
                                                                data-bs-placement="top"
                                                                data-bs-custom-class="custom-tooltip"
                                                                data-bs-title="{{ __('Restore Testimonial') }}"
                                                                href="{{ route('testimonial.restore', $testimonial->id) }}"><i
                                                                    class="bi bi-arrow-counterclockwise Circleicon"></i></a>
                                                        @else
                                                            <a class="circleIcon" data-bs-toggle="tooltip"
                                                                data-bs-placement="top"
                                                                data-bs-custom-class="custom-tooltip"
                                                                data-bs-title="{{ __('Edit Testimonial') }}"
                                                                href="{{ route('testimonial.edit', $testimonial->id) }}">
                                                                <img src="{{ asset('assets/images/icon/edit.svg') }}"
                                                                    alt="icon">
                                                            </a>
                                                            <a class="circleIcon" data-bs-toggle="tooltip"
                                                                data-bs-placement="top"
                                                                data-bs-custom-class="custom-tooltip"
                                                                data-bs-title="Delete Testimonial" href="#"
                                                                onclick="deleteAction('{{ route('testimonial.destroy', $testimonial->id) }}')">
                                                                <img src="{{ asset('assets/images/icon/trash.svg') }}"
                                                                    alt="icon">
                                                            </a>
                                                        @endif
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="8">
                                                    <h5 class="text-danger text-center m-0">{{ __('No Queries Available') }}</h5>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            {{ $testimonials->links() }}
                        </div>
                    </div>
                </div>
            </div>

            {{-- bottom --}}
        </div>
    </div>


@endsection
