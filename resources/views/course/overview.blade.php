@extends('layouts.app')

@section('title', $app_setting['name'] . ' | '.__('Course Overview'))

@section('content')

    <div class="app-main-outer">
        <div class="app-main-inner">
            {{-- top --}}

            <div class="page-title-actions px-3 d-flex">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }}</a></li>
                        <li class="breadcrumb-item active" aria-current="page">{{ __('Overview') }}</li>
                    </ol>
                </nav>
            </div>

            <div class="bg-white my-4 p-3 rounded-3">
                <div class="row">
                    <div class="col-lg-11">
                        <div class="mb-3">
                            <p class="text-muted">{{ __('published on') }} {{ $course?->created_at->diffForHumans() }}</p>
                        </div>
                        <div>
                            <span
                                class="text-primary bg-primary-light mt-2 p-2 rounded">{{ $course?->category?->title }}</span>
                            <h3 class="mt-3 mb-5 fw-bold fs-4 ">{{ $course?->title }}</h3>
                        </div>
                    </div>
                    <div class="col-lg-1">
                        <p>Views : <span class="fw-bold badge bg-primary"> {{ $course->view_count }}</span></p>
                    </div>
                    <div class="col-md-6 col-xl-3">
                        <div class="card widget-content bg-gray">
                            <div class="widget-content-wrapper text-white">
                                <div class="widget-content-left">
                                    <div class="widget-heading text-color-blue">{{ currency($course?->regular_price) }}
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center text-color-gray">
                                        <div class="widget-subheading">{{ __('Regular Price') }}</div>
                                        <div class="widget-icon">
                                            <img src="{{ asset('assets/images/card/book-open-text.svg') }}" alt="icon">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6 col-xl-3">
                        <div class="card widget-content bg-gray">
                            <div class="widget-content-wrapper text-white">
                                <div class="widget-content-left">
                                    <div class="widget-heading text-color-purple"> {{ currency($course?->price ?? 0.0) }}
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center text-color-gray">
                                        <div class="widget-subheading">{{ __('Offer Price') }}</div>
                                        <div class="widget-icon">
                                            <img src="{{ asset('assets/images/card/students.svg') }}" alt="icon">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>


                    <div class="col-md-6 col-xl-3 mt-3 mt-xl-0">
                        <div class="card  widget-content bg-gray">
                            <div class="widget-content-wrapper text-white">
                                <div class="widget-content-left">
                                    <div class="widget-heading text-color-red">{{ $enrollments }}</div>
                                    <div class="d-flex justify-content-between align-items-center text-color-gray">
                                        <div class="widget-subheading">{{ __('Total Enrollments')}} ({{ __('Lifetime') }})</div>
                                        <div class="widget-icon">
                                            <img src="{{ asset('assets/images/card/users-group.svg') }}" alt="icon">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6 col-xl-3 mt-3 mt-xl-0">
                        <div class="card widget-content bg-gray">
                            <div class="widget-content-wrapper text-white">
                                <div class="widget-content-left">
                                    <div class="widget-heading text-color-green">
                                        {{ currency($transactions) }}
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center text-color-gray">
                                        <div class="widget-subheading">{{ __('Total Transaction Amount') }}</div>
                                        <div class="widget-icon">
                                            <img src="{{ asset('assets/images/card/invoice.svg') }}" alt="icon">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-lg-7">
                                    <ul class="nav nav-pills mb-3 bg-gray p-3 rounded-pill d-flex justify-content-between align-items-center"
                                        id="pills-tab" role="tablist">
                                        <li class="nav-item" role="presentation">
                                            <button class="nav-link active" id="pills-about-tab" data-bs-toggle="pill"
                                                data-bs-target="#pills-about" type="button" role="tab"
                                                aria-controls="pills-about" aria-selected="true">{{ __('About') }}</button>
                                        </li>
                                        <li class="nav-item" role="presentation">
                                            <button class="nav-link" id="pills-home-tab" data-bs-toggle="pill"
                                                data-bs-target="#pills-lession" type="button" role="tab"
                                                aria-controls="pills-lession" aria-selected="false">{{ __('Lessons') }}</button>
                                        </li>
                                        <li class="nav-item" role="presentation">
                                            <button class="nav-link" id="pills-reviews-tab" data-bs-toggle="pill"
                                                data-bs-target="#pills-reviews" type="button" role="tab"
                                                aria-controls="pills-reviews" aria-selected="false">{{ __('Reviews') }}</button>
                                        </li>
                                        <li class="nav-item" role="presentation">
                                            <button class="nav-link" id="pills-students-tab" data-bs-toggle="pill"
                                                data-bs-target="#pills-students" type="button" role="tab"
                                                aria-controls="pills-students" aria-selected="false">{{ __('Students') }}</button>
                                        </li>
                                    </ul>
                                    <div class="tab-content" id="pills-tabContent">
                                        {{-- ABOUT TAB --}}
                                        <div class="tab-pane fade show active" id="pills-about" role="tabpanel"
                                            aria-labelledby="pills-about-tab" tabindex="0">
                                            <div class="card">
                                                @php
                                                    $descriptions = json_decode($course->description);
                                                @endphp
                                                @if (!empty($descriptions))
                                                    @foreach (json_decode($course->description) as $description)
                                                        <div class="card-body">
                                                            <h5 class="card-title">{{ $description->heading }}</h5>
                                                            <p class="card-text">
                                                                {!! $description->body !!}
                                                            </p>
                                                        </div>
                                                    @endforeach
                                                @endif
                                            </div>
                                        </div>
                                        {{-- ABOUT TAB --}}

                                        {{-- LESSION START --}}
                                        <div class="tab-pane fade" id="pills-lession" role="tabpanel"
                                            aria-labelledby="pills-lession-tab" tabindex="0">
                                            <div class="row">
                                                <div class="col-lg-12 mb-3">
                                                    <h4 class="m-0">{{ __('Lessions') }}</h4>
                                                </div>
                                            </div>
                                            <div class="card">
                                                <div class="card-body">
                                                    <div class="accordion" id="accordionExample">
                                                        @forelse ($chapters as $chapter)
                                                            <div class="accordion-item rounded-3 my-4">
                                                                <h2 class="accordion-header">
                                                                    <button class="accordion-button rounded-3"
                                                                        type="button" data-bs-toggle="collapse"
                                                                        data-bs-target="#collapseOne{{ $chapter->id }}"
                                                                        aria-expanded="true"
                                                                        aria-controls="collapseOne{{ $chapter->id }}">
                                                                        <div class="accordion-content w-100">
                                                                            <div
                                                                                class="d-flex justify-content-between align-items-center mb-3">
                                                                                <small class="me-2 text-muted">
                                                                                    {{ __('Class') }} {{ $loop->iteration }}
                                                                                </small>
                                                                                <small
                                                                                    class="me-2 text-muted chapter-duration chapter-duration">
                                                                                    {{ __('Duration') }} :
                                                                                    @php
                                                                                        $totalDuration = $chapter->contents->sum(
                                                                                            'duration',
                                                                                        );
                                                                                        $hours = floor(
                                                                                            $totalDuration / 60,
                                                                                        );
                                                                                        $minutes = $totalDuration % 60;
                                                                                    @endphp

                                                                                    @if ($hours > 0 && $minutes > 0)
                                                                                        {{ $hours ?? 0 }} {{ __('hours') }}
                                                                                        {{ $minutes }} {{ __('minutes') }}
                                                                                    @else
                                                                                        {{ $totalDuration ?? 0 }}
                                                                                        {{ __('minutes') }}
                                                                                    @endif
                                                                                </small>
                                                                            </div>
                                                                            <p class="text-capitalize">
                                                                                {{ $chapter->title }}
                                                                            </p>

                                                                        </div>
                                                                    </button>
                                                                </h2>

                                                                <div id="collapseOne{{ $chapter->id }}"
                                                                    class="accordion-collapse collapse"
                                                                    data-bs-parent="#accordionExample{{ $chapter->id }}">
                                                                    @forelse ($chapter->contents as $content)
                                                                        <div
                                                                            class="accordion-body d-flex justify-content-between align-items-center">
                                                                            <div
                                                                                class="d-flex gap-2 align-items-center text-capitalize">
                                                                                <i
                                                                                    class="bi {{ $content->filterType($content->type) }}"></i>
                                                                                <strong>{{ $content->title }}.</strong>
                                                                            </div>
                                                                            @php
                                                                                $totalDuration = $content->duration;
                                                                                $hours = floor($totalDuration / 60);
                                                                                $minutes = $totalDuration % 60;
                                                                            @endphp
                                                                            <small
                                                                                class="me-2 text-muted chapter-duration">
                                                                                @if ($hours > 0 && $minutes > 0)
                                                                                    {{ $hours ?? 0 }} {{ __('hours') }}
                                                                                    {{ $minutes }} {{ __('minutes') }}
                                                                                @else
                                                                                    {{ $totalDuration ?? 0 }}
                                                                                    {{ __('minutes') }}
                                                                                @endif
                                                                            </small>
                                                                        </div>
                                                                    @empty
                                                                        <div
                                                                            class="bg-white d-flex justify-content-center p-1">
                                                                            <span class="text-center text-danger">
                                                                                {{ __('no content found') }}!!
                                                                            </span>
                                                                        </div>
                                                                    @endforelse
                                                                </div>
                                                            </div>
                                                        @empty
                                                            <div class="bg-white d-flex justify-content-center p-1">
                                                                <span class="text-center text-danger">
                                                                    {{ __('no course found') }}!!
                                                                </span>
                                                            </div>
                                                        @endforelse
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        {{-- LESSION END --}}

                                        {{-- REVIEW START --}}
                                        <div class="tab-pane fade" id="pills-reviews" role="tabpanel"
                                            aria-labelledby="pills-reviews-tab" tabindex="0">
                                            <div class="row">
                                                <div class="col-lg-12 mb-3">
                                                    <h4 class="m-0">{{ __('Reviews') }}</h4>
                                                </div>
                                            </div>
                                            @forelse ($reviews as $review)
                                                <div class="bg-white rounded-3 p-1">

                                                    <div>
                                                        <div class="border rounded-3 px-4 py-1">
                                                            <div class="d-flex justify-content-between align-items-center">
                                                                <div class="d-flex mt-3 pb-3">
                                                                    <img src="{{ $review?->user?->profilePicturePath }}"
                                                                        class="rounded-circle object-fit-cover me-3"
                                                                        height="55px" width="55px">
                                                                    <div>
                                                                        <span
                                                                            class="d-block">{{ $review?->user?->name }}</span>
                                                                        <i
                                                                            class="bi bi-star-fill text-warning me-2"></i><small
                                                                            class="fw-bold">7</small>
                                                                    </div>
                                                                </div>
                                                                <span
                                                                    class="text-muted">{{ \Carbon\Carbon::parse($review?->created_at)->format('d M Y') }}</span>
                                                            </div>
                                                            <p>{{ $review?->comment }}</p>
                                                        </div>
                                                    </div>
                                                </div>
                                            @empty
                                                <div class="bg-white d-flex justify-content-center p-1">
                                                    <span class="text-center text-danger">
                                                        {{ __('no students found') }}!!
                                                    </span>
                                                </div>
                                            @endforelse
                                        </div>
                                        {{-- REVIEW END --}}
                                        {{-- students start --}}
                                        <div class="tab-pane fade" id="pills-students" role="tabpanel"
                                            aria-labelledby="pills-students-tab" tabindex="0">
                                            <div class="row">
                                                <div class="col-lg-12 mb-3">
                                                    <h4 class="m-0">{{ __('Students') }}</h4>
                                                </div>
                                            </div>
                                            @forelse ($students as $student)
                                                <div class="bg-white rounded-3 p-1">
                                                    <div>
                                                        <div class="border rounded-3 px-4 py-1">
                                                            <div class="d-flex justify-content-between align-items-center">
                                                                <div class="d-flex mt-3 pb-3">
                                                                    <img src="{{ $student?->user?->profilePicturePath }}"
                                                                        class="rounded-circle object-fit-cover me-3"
                                                                        height="55px" width="55px">
                                                                    <div>
                                                                        <span
                                                                            class="d-block">{{ $student?->user?->name }}</span>
                                                                        <i
                                                                            class="bi bi-star-fill text-warning me-2"></i><small
                                                                            class="fw-bold">{{ $student?->user?->phone }}</small>
                                                                    </div>
                                                                </div>
                                                                <span
                                                                    class="text-muted">{{ \Carbon\Carbon::parse($student?->created_at)->format('d M Y') }}</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @empty
                                                <div class="bg-white d-flex justify-content-center p-1">
                                                    <span class="text-center text-danger">
                                                        {{ __('no students found') }}!!
                                                    </span>
                                                </div>
                                            @endforelse

                                            {{ $students->links() }}
                                        </div>
                                        {{-- students end --}}
                                    </div>
                                </div>
                                <div class="col-lg-5 mt-3 mt-lg-0">
                                    <div class="card p-4">
                                        <img src="{{ $course->mediaPath }}" class="card-img-top object-fit-contain"
                                            alt="img" style="width: 100%; height: 300px;">
                                        <div class="card-body p-0 pt-4">
                                            <div
                                                class="d-flex justify-content-between align-items-center py-4 border-bottom">
                                                <h5 class="m-0 text-dark">{{ __('Total Class') }}</h5>
                                                {{-- <div>
                                                    <i class="bi bi-star-fill text-warning me-2"></i><small
                                                        class="fw-bold">7</small>
                                                </div> --}}
                                                <span class='text-muted'>
                                                    <strong>{{ $countClass }}</strong>
                                                </span>
                                            </div>
                                            <div
                                                class="d-flex justify-content-between align-items-center py-4 border-bottom">
                                                <h5 class="m-0 text-dark">{{ __('Total Duration') }}</h5>
                                                <span class='text-muted'> @php
                                                    $totalDuration = $durationCount;
                                                    $hours = floor($totalDuration / 60);
                                                    $minutes = $totalDuration % 60;
                                                @endphp

                                                    @if ($hours > 0)
                                                        {{ $hours }} {{ __('hours') }}
                                                    @endif

                                                    @if ($minutes > 0)
                                                        {{ $minutes ?? 0 }} {{ __('minutes') }}
                                                    @endif

                                                    @if ($hours == 0 && $minutes == 0)
                                                        N/A
                                                    @endif

                                                </span>
                                            </div>
                                            <div
                                                class="d-flex justify-content-between align-items-center py-4 border-bottom">
                                                <h5 class="m-0 text-dark">{{ __('Total Videos') }}</h5>
                                                <span class='text-muted'><strong>{{ $videoCount }}</strong></span>
                                            </div>
                                            <div
                                                class="d-flex justify-content-between align-items-center py-4 border-bottom">
                                                <h5 class="m-0 text-dark">{{ __('Total Audios') }}</h5>
                                                <span class='text-muted'><strong>{{ $audioCount }}</strong></span>
                                            </div>
                                            <div
                                                class="d-flex justify-content-between align-items-center py-4 border-bottom">
                                                <h5 class="m-0 text-dark">{{ __('Total Images') }}</h5>
                                                <span class='text-muted'><strong>{{ $imageCount }}</strong></span>
                                            </div>
                                            <div class="d-flex justify-content-between align-items-center py-4">
                                                <h5 class="m-0 text-dark">{{ __('Total Free Chapter') }}</h5>
                                                <span class='text-muted'><strong>{{ $freeContentCount }}</strong></span>
                                            </div>
                                            <div class="d-flex flex-column justify-content-center align-items-center mt-3 rounded-3"
                                                style="height: 120px; width: 100%; background-color: #e0f0fe">
                                                <h3 class="fw-semibold">{{ __('Total Course Price') }}</h3>
                                                @if ($course?->price && $course?->regular_price)
                                                    <p class="fw-bold fs-5">{{ currency($course?->price) }} <span
                                                            class="text-muted text-decoration-line-through fs-6">{{ currency($course?->regular_price) }}</span>
                                                    </p>
                                                @else
                                                    <p class="fw-bold fs-5">{{ currency($course?->regular_price) }}</p>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>


            {{-- bottom --}}
        </div>

    </div>
@endsection
