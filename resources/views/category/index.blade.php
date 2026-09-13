@extends('layouts.app')

@section('title', $app_setting['name'] . ' | ' . __('Category List'))

@section('content')
    <!-- ****Body-Section***** -->
    <div class="app-main-outer">
        <div class="app-main-inner">
            <div
                class="page-title-actions px-3 py-3 d-flex justify-content-between align-items-center bg-white rounded mb-3">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb m-0 p-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }}</a></li>
                        <li class="breadcrumb-item active" aria-current="page">{{ __('Category') }}</li>
                    </ol>
                </nav>
                <div class="ms-auto">
                    <a href="{{ route('category.create') }}" class="btn btn-shadow btn-outline-primary mr-3 ms-auto">
                        +{{ __('New Category') }}
                    </a>
                </div>
            </div>

            <div class="row" id="deleteTableItem">
                <div class="col-md-12">
                    <div class="card mb-5">
                        <div class="card-body">
                            <div class=" d-flex justify-content-end mb-3 align-items-center">
                                <form action="{{ route('category.index') }}" method="GET" class="w-25 me-2">
                                    <div class="input-group">
                                        <input type="text" class="form-control" id="inputGroupFile04"
                                            aria-describedby="inputGroupFileAddon04" aria-label="Upload"
                                            placeholder="{{ __('Search') }}" name="cat_search"
                                            value="{{ request('cat_search') }}">
                                        <button class="btn btn-outline-primary px-3" type="submit"
                                            id="inputGroupFileAddon04"><i class="bi bi-search"></i></button>
                                    </div>
                                </form>
                                <div class="d-flex justify-content-end">
                                    <a href="{{ route('category.index') }}" class="px-3">
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
                                            <th><strong>{{ __('Title') }}</strong></th>
                                            <th><strong>{{ __('Featured') }}</strong></th>
                                            <th><strong>{{ __('Color') }}</strong></th>
                                            <th><strong>{{ __('Status') }}</strong></th>
                                            <th><strong>{{ __('Action') }}</strong></th>
                                        </tr>
                                    </thead>
                                    <tbody id="shortableCategory">
                                        @forelse ($categories as $category)
                                            <tr data-id="{{ $category->id }}">
                                                <td>{{ $categories->firstItem() + $loop->index }}</td>
                                                <td>
                                                    <div class="d-flex align-items-center justify-content-start">
                                                        <img src="{{ $category->imagePath }}" alt="image" width="80"
                                                            height="80" style="border-radius: 16px; object-fit: cover">
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="product-pera">
                                                        <p class="priceDis">{{ $category->title }}</p>
                                                    </div>
                                                </td>

                                                <td>
                                                    @if ($category->is_featured)
                                                        <span
                                                            class="badge rounded-pill text-bg-success">{{ __('Yes') }}</span>
                                                    @else
                                                        <span
                                                            class="badge rounded-pill text-bg-danger">{{ __('No') }}</span>
                                                    @endif
                                                </td>

                                                <td><span class="px-3 py-1 rounded"
                                                        style="background-color: {{ $category->color }}"></span> &nbsp;
                                                    {{ $category->color }}</td>

                                                <td>
                                                    @if ($category->trashed())
                                                        <div
                                                            class="statusItem d-flex justify-content-center align-items-center">
                                                            <div class="circleDot animatedPending"></div>
                                                            <div class="statusText">
                                                                <span class="stutsPanding">{{ __('Deleted') }}</span>
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
                                                        class="d-flex justify-content-center align-items-center gap-3 flex-wrap">
                                                        @if ($category->trashed())
                                                            <a class="circleIcon" data-bs-toggle="tooltip"
                                                                data-bs-placement="top"
                                                                data-bs-custom-class="custom-tooltip"
                                                                data-bs-title="{{ __('Restore Category') }}"
                                                                href="{{ route('category.restore', $category->id) }}"><i
                                                                    class="bi bi-arrow-counterclockwise Circleicon"></i></a>
                                                        @else
                                                            <a class="circleIcon" data-bs-toggle="tooltip"
                                                                data-bs-placement="top"
                                                                data-bs-custom-class="custom-tooltip"
                                                                data-bs-title="{{ __('Edit Category') }}"
                                                                href="{{ route('category.edit', $category->id) }}">
                                                                <img src="{{ asset('assets/images/icon/edit.svg') }}"
                                                                    alt="icon">
                                                            </a>
                                                            <a class="circleIcon" data-bs-toggle="tooltip"
                                                                data-bs-placement="top"
                                                                data-bs-custom-class="custom-tooltip"
                                                                data-bs-title="{{ __('Delete Category') }}" href="#"
                                                                onclick="deleteAction('{{ route('category.destroy', $category->id) }}')">
                                                                <img src="{{ asset('assets/images/icon/trash.svg') }}"
                                                                    alt="icon">
                                                            </a>
                                                            <a class="circleIcon" data-bs-toggle="tooltip"
                                                                data-bs-placement="top"
                                                                data-bs-custom-class="custom-tooltip"
                                                                data-bs-title="{{ __('Move Category') }}"
                                                                href="javascript:void(0)">
                                                                <img src="{{ asset('assets/images/icon/move.svg') }}"
                                                                    alt="icon">
                                                            </a>
                                                        @endif
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="8">
                                                    <h5 class="text-danger text-center m-0">
                                                        {{ __('No Category Available') }}
                                                    </h5>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            {{ $categories->links() }}
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
        var el = document.getElementById('shortableCategory');
        var sortable = Sortable.create(el, {
            animation: 150,
            onEnd: function(evt) {
                let sortedIds = [];
                document.querySelectorAll('#shortableCategory tr').forEach((row, index) => {
                    sortedIds.push({
                        id: row.getAttribute('data-id'),
                        display_order: index + 1
                    });
                });

                fetch('{{ route('category.sort') }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            sortedCategories: sortedIds
                        })
                    }).then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            const Toast = Swal.mixin({
                                toast: true,
                                position: "top-end",
                                showConfirmButton: false,
                                timer: 3000,
                                timerProgressBar: true,
                                didOpen: (toast) => {
                                    toast.onmouseenter = Swal.stopTimer;
                                    toast.onmouseleave = Swal.resumeTimer;
                                }
                            });
                            Toast.fire({
                                icon: "success",
                                title: "{{ __('Table Order Update Successfully') }}"
                            });
                        } else {
                            alert('Failed to update order');
                        }
                    });
            }
        });
    </script>
@endpush
