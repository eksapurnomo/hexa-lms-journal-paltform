@extends('layouts.app')

@section('title', $app_setting['name'] . ' | '.__('Dashboard'))

@section('content')

    <!-- ****Body-Section***** -->
    <div class="app-main-outer">
        <div class="app-main-inner">
            <div class="container-fluid">
                <div class="bg-white my-4 p-3 rounded-3">
                    <div class="row">
                        <div class="col-md-6 col-xl-4">
                            <div class="card mt-3 mt-lg-0 widget-content bg-gray">
                                <div class="widget-content-wrapper text-white">
                                    <div class="widget-content-left">
                                        <div class="widget-heading text-color-blue">{{ $active_course_count }}</div>
                                        <div class="d-flex justify-content-between align-items-center text-color-gray">
                                            <div class="widget-subheading">{{ __('Active courses') }}</div>
                                            <div class="widget-icon">
                                                <img src="{{ asset('assets/images/card/book-open-text.svg') }}"
                                                    alt="icon">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6 col-xl-4">
                            <div class="card mt-3 mt-lg-0 widget-content bg-gray">
                                <div class="widget-content-wrapper text-white">
                                    <div class="widget-content-left">
                                        <div class="widget-heading text-color-purple">{{ $enrollment_count }}</div>
                                        <div class="d-flex justify-content-between align-items-center text-color-gray">
                                            <div class="widget-subheading">{{ __('Total Course Enrollments') }}</div>
                                            <div class="widget-icon">
                                                <img src="{{ asset('assets/images/card/students.svg') }}" alt="icon">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>


                        <div class="col-md-6 col-xl-4">
                            <div class="card mt-3 mt-xl-0 widget-content bg-gray">
                                <div class="widget-content-wrapper text-white">
                                    <div class="widget-content-left">
                                        <div class="widget-heading text-color-red">{{ $student_count }}</div>
                                        <div class="d-flex justify-content-between align-items-center text-color-gray">
                                            <div class="widget-subheading">{{ __('Total Students') }}</div>
                                            <div class="widget-icon">
                                                <img src="{{ asset('assets/images/card/users-group.svg') }}" alt="icon">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6 col-xl-4">
                            <div class="card mt-3 widget-content bg-gray">
                                <div class="widget-content-wrapper text-white">
                                    <div class="widget-content-left">
                                        <div class="widget-heading text-color-light-green">{{ $instructor_count }}</div>
                                        <div class="d-flex justify-content-between align-items-center text-color-gray">
                                            <div class="widget-subheading">{{ __('Total Instructors') }}</div>
                                            <div class="widget-icon">
                                                <img src="{{ asset('assets/images/card/teacher.svg') }}" alt="icon">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6 col-xl-4">
                            <div class="card mt-3 widget-content bg-gray">
                                <div class="widget-content-wrapper text-white">
                                    <div class="widget-content-left">
                                        <div class="widget-heading text-color-green">
                                            {{ currency($transaction_amount) }}
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

                        <div class="col-md-6 col-xl-4">
                            <div class="card mt-3 widget-content bg-gray">
                                <div class="widget-content-wrapper text-white">
                                    <div class="widget-content-left">
                                        <div class="widget-heading text-color-orange">{{ $review_count }}</div>
                                        <div class="d-flex justify-content-between align-items-center text-color-gray">
                                            <div class="widget-subheading">{{ __('Total submitted reviews') }}</div>
                                            <div class="widget-icon">
                                                <img src="{{ asset('assets/images/card/receipt-star.svg') }}"
                                                    alt="icon">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>



                <!-- statistics Overview -->
                <div class="card mt-3">
                    <div class="card-body">
                        <div class="cardTitleBox d-flex align-items-center justify-content-between flex-wrap gap-2">
                            <h5 class="card-title chartTitle mb-0">{{ __('Course & User Statistics') }}</h5>
                            <div class="d-flex align-items-center gap-3 flex-wrap">
                                <div class="d-flex align-items-center flex-wrap gap-2">
                                    <button class="statisticsBtn active" data-value="daily">
                                        {{ __('Daily') }}
                                    </button>
                                    <button class="statisticsBtn" data-value="monthly">
                                        {{ __('Monthly') }}
                                    </button>
                                    <button class="statisticsBtn" data-value="yearly">
                                        {{ __('Yerly') }}
                                    </button>
                                </div>

                                <div class="statisticsDivder"></div>

                                <div>
                                    <input type="date" name="date" class="statisticsInput">
                                </div>

                            </div>
                        </div>

                        <div class="row">
                            <div class="col-lg-12 col-xl-8">

                                <div class="card">
                                    <div class="card-body">
                                        <div class="border-bottom pb-3">
                                            <h3 id="totalSele">0</h3>
                                            <p>{{ __('Course Sele Overview') }}</p>
                                        </div>
                                        <div id="chart" width="400" height="200"></div>
                                    </div>
                                </div>

                            </div>

                            <div class="col-lg-12 col-xl-4">
                                <div class="card h-100 border">
                                    <div class="card-body d-flex flex-column justify-content-between">
                                        <div class="border-bottom pb-3">
                                            <h3>{{ $total_users_count }}</h3>
                                            <p>{{ __('New User Overview') }}</p>
                                        </div>

                                        <div class="my-auto">
                                            <canvas id="donut" width="400" height="400"></canvas>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>


                <div class="card my-3">
                    <div class="card-body">
                        <div class="cardTitleBox">
                            <h5 class="card-title chartTitle">{{ __('Top Selling Course') }} <span>( {{ __('Latest') }}
                                    {{ $popular_courses->count() }} )</span></h5>
                            <a href="{{ route('course.index') }}" class="charNavigation">
                                {{ __('View All Courses') }}
                            </a>
                        </div>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th><strong>{{ __('ID') }}</strong></th>
                                        <th><strong>{{ __('Course') }}</strong></th>
                                        <th><strong>{{ __('Category') }}</strong></th>
                                        <th><strong>{{ __('Views') }}</strong></th>
                                        <th><strong>{{ __('Price') }}</strong></th>
                                        <th><strong>{{ __('Instructor') }}</strong></th>
                                        <th><strong>{{ __('Action') }}</strong></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($popular_courses as $course)
                                        <tr>
                                            <td>#{{ $course->id }} <i data-feather="eye"></i>
                                            </td>
                                            <td>
                                                <div class="listproduct-section">
                                                    <div class="listproducts-image">
                                                        <img src="{{ $course->mediaPath }}">
                                                    </div>
                                                    <div class="product-pera">
                                                        <p class="priceDis">{{ $course->title }}</p>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>{{ $course->category?->title }}
                                            </td>
                                            <td>{{ $course->view_count }}</td>
                                            <td>
                                                ${{ $course->price && $course->regular_price ? $course->price : $course->regular_price }}
                                            </td>
                                            <td>{{ $course->instructor->user->name }}</td>
                                            <td>
                                                <div class="d-flex flex-wrap align-items-center gap-2">
                                                    <a data-bs-toggle="tooltip" data-bs-placement="top"
                                                        data-bs-custom-class="custom-tooltip" data-bs-title="{{ __('Edit Course') }}"
                                                        href="{{ route('course.edit', $course->id) }}"
                                                        class="circleIcon">
                                                        <img src="{{ asset('assets/images/icon/edit.svg') }}"
                                                            alt="icon">
                                                    </a>
                                                    <a href="#" data-bs-toggle="tooltip" data-bs-placement="top"
                                                        data-bs-custom-class="custom-tooltip"
                                                        data-bs-title="{{ __('Delete Course') }}"
                                                        onclick="deleteAction('{{ route('course.destroy', $course->id) }}')"
                                                        class="circleIcon">
                                                        <img src="{{ asset('assets/images/icon/trash.svg') }}"
                                                            alt="icon">
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7">
                                                <h5 class="text-danger text-center m-0">
                                                    {{ __('No data Available') }}
                                                </h5>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>


                <div class="row mb-5">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-body">
                                <div class="cardTitleBox">
                                    <h5 class="card-title chartTitle">{{ __('Top') }} {{ $topStudents?->count() }} {{ __('Student') }}</h5>
                                </div>
                                @forelse ($topStudents as $student)
                                    <div
                                        class="infocard mb-2 d-flex flex-wrap justify-content-between align-items-center gap-2 border rounded p-2">
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="infocard-image">
                                                <img src="{{ $student?->profilePicturePath }}" alt="avatar">
                                            </div>
                                            <div class="infodescription">
                                                <div class="infocard-name">{{ $student?->name }}</div>
                                                <div class="infocard-email">{{ $student?->email }}</div>
                                            </div>
                                        </div>
                                        <div>
                                            <div class="border rounded p-2">
                                                <p class="infocard-count">{{ __('Buy Courses') }} : {{ $student?->enrollments_count }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div
                                        class="infocard mb-2 d-flex justify-content-between align-items-center gap-2 border rounded p-2">
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="infocard-image">
                                                <img src="{{ asset('assets/images/avatars/avata01.png') }}"
                                                    alt="avatar">
                                            </div>
                                            <div class="infodescription">
                                                <div class="infocard-name">{{ __('No Students Found') }}!</div>
                                                <div class="infocard-email">5hBzU@example.com</div>
                                            </div>
                                        </div>
                                        <div>
                                            <p class="infocard-count">{{ __('Total Courses') }} : 0</p>
                                            <div class="icons d-flex justify-content-between align-items-center">
                                                <img src="{{ asset('assets/images/icon/eye.svg') }}" alt="icon">
                                                <img src="{{ asset('assets/images/icon/edit.svg') }}" alt="icon">
                                                <img src="{{ asset('assets/images/icon/trash.svg') }}" alt="icon">
                                            </div>
                                        </div>
                                    </div>
                                @endforelse
                            </div>

                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-body">
                                <div class="cardTitleBox">
                                    <h5 class="card-title chartTitle">{{ __('Top') }} {{ $topInstructors?->count() }} {{ __('Instructor') }}</h5>
                                </div>

                                @forelse ($topInstructors as $instructor)
                                    <div
                                        class="infocard mb-2 d-flex flex-wrap justify-content-between align-items-center gap-2 border rounded p-2">
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="infocard-image">
                                                <img src="{{ $instructor?->user?->profilePicturePath }}" alt="avatar">
                                            </div>
                                            <div class="infodescription">
                                                <div class="infocard-name">{{ $instructor?->user?->name }}</div>
                                                <div class="infocard-icon">
                                                    @php
                                                        $rating = $instructor?->reviews_avg_rating;
                                                        $rating = number_format($rating, 1);

                                                    @endphp
                                                    @for ($index = 0; $index < 5; $index++)
                                                        @if ($rating > $index)
                                                            <i class="fa-solid fa-star"></i>
                                                        @else
                                                            <i class="fa-regular fa-star"></i>
                                                        @endif
                                                    @endfor
                                                    <span class="infocard-rating ms-1">
                                                        {{ number_format($instructor?->reviews_avg_rating, 1) }}
                                                        <span
                                                            class="infocard-rating-count">({{ $instructor?->reviews_count }})</span>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="border rounded p-2">
                                            <p class="infocard-count">{{ __('Total Courses') }} :
                                                <strong>{{ $instructor?->courses_count }}</strong>
                                            </p>
                                        </div>
                                    </div>
                                @empty
                                    <div
                                        class="infocard mb-1 d-flex justify-content-between align-items-center border rounded p-2">
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="infocard-image">
                                                <img src="{{ asset('assets/images/avatars/avata01.png') }}"
                                                    alt="avatar">
                                            </div>
                                            <div class="infodescription">
                                                <div class="infocard-name">{{ __('No Instructor Found') }}!</div>
                                                <div class="infocard-icon">
                                                    <i class="fa-solid fa-star"></i>
                                                    <i class="fa-solid fa-star"></i>
                                                    <i class="fa-solid fa-star"></i>
                                                    <i class="fa-solid fa-star"></i>
                                                    <i class="fa-solid fa-star"></i>
                                                    <i class="fa-solid fa-star"></i>
                                                    <span class="infocard-rating ms-1">
                                                        0.0
                                                        <span class="infocard-rating-count">(0)</span>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                        <div>
                                            <p class="infocard-count">{{ __('Buy Courses') }} : 0</p>
                                            <div class="icons d-flex justify-content-between align-items-center">
                                                <img src="{{ asset('assets/images/icon/eye.svg') }}" alt="icon">
                                                <img src="{{ asset('assets/images/icon/edit.svg') }}" alt="icon">
                                                <img src="{{ asset('assets/images/icon/trash.svg') }}" alt="icon">
                                            </div>
                                        </div>
                                    </div>
                                @endforelse


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
        var currentSitatics = 'daily';
        var chart = null;

        $('.statisticsBtn').on('click', function() {
            $('.statisticsBtn').removeClass('active');
            $(this).addClass('active');
            var sitatics = $(this).data('value');

            if (sitatics != currentSitatics) {
                currentSitatics = sitatics;
                fetchOrdersChart();
            }
        });

        const fetchOrdersChart = () => {
            $.ajax({
                url: "{{ route('admin.statistics') }}",
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    type: currentSitatics,
                    'author_id': "{{ Auth::user()->id }}"
                },
                success: (response) => {
                    console.log(response.data.labels); // Debug response

                    var chartLabels = response.data.labels || [];
                    var chartData = response.data.values || [];

                    if (chart) {
                        chart.destroy();
                    }

                    loadChart(chartLabels, chartData);
                    $('#totalSele').text(response.data.total || 0);
                },
            });
        };

        const loadChart = (chartLabels, chartData) => {

            var options = {
                series: [{
                    name: 'Orders',
                    data: chartData,
                }],
                colors: ['#9E4AED'],
                chart: {
                    height: 400,
                    type: 'area',
                    animations: {
                        enabled: true,
                        easing: 'easeinout',
                        speed: 800,
                    },
                },
                dataLabels: {
                    enabled: false,
                },
                stroke: {
                    curve: 'smooth',
                },
                labels: chartLabels,
                tooltip: {
                    x: {
                        format: 'dd/MM/yy HH:mm',
                    },
                },
            };

            chart = new ApexCharts(document.querySelector("#chart"), options);
            chart.render();

        };

        fetchOrdersChart();
    </script>

    <script>
        const ctx = document.getElementById('donut').getContext('2d');

        const data = {
            labels: ["{{ $student_count }} Students", "{{ $instructor_count }} Teacher",
                "{{ $active_course_count }} Course", "{{ $enrollment_count }} Enrollments"
            ],
            datasets: [{
                data: [{{ $student_count }}, {{ $instructor_count }}, {{ $active_course_count }},
                    {{ $enrollment_count }}
                ],
                borderWidth: 1,
                backgroundColor: ['#5864FF', '#318E55', '#067BFF', '#FFC107'],
            }]
        };

        new Chart(ctx, {
            type: 'doughnut',
            data: data,
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    </script>
@endpush
