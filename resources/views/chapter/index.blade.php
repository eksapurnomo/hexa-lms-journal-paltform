@extends('layouts.app')

@section('title', $app_setting['name'] . ' | Chapter List')

@section('content')
    <!-- ****Body-Section***** -->
    <div class="app-main-outer">
        <div class="app-main-inner">
            <div
                class="page-title-actions px-3 py-3 d-flex justify-content-between align-items-center bg-white rounded mb-3">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb m-0 p-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('course.index') }}">Course</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Chapter</li>
                    </ol>
                </nav>
            </div>

            <div class="mb-3 bg-primary-light text-dark p-4 rounded">
                <div class="row align-items-center">
                    <div class="col-md-6 text-wrap">
                        <h4 class="m-0">Showing chapters for: <strong>{{ $course->title }}</strong></h4>
                    </div>
                    <div class="col-md-6">
                        <div class="d-flex justify-content-end">
                            <a href="{{ route('chapter.create', $course->id) }}"
                                class="btn btn-shadow btn-outline-primary mr-3 ms-auto">
                                {{ __('+ New Chapter') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row" id="deleteTableItem">
                <div class="col-md-12">
                    <div class="card mb-5">
                        <div class="card-body">
                            <div class=" d-flex justify-content-end mb-3 align-items-center">
                                <form action="{{ route('chapter.index', $course->id) }}" method="GET" class="w-25 me-2">
                                    <div class="input-group">
                                        <input type="text" class="form-control" id="inputGroupFile04"
                                            aria-describedby="inputGroupFileAddon04" aria-label="Upload"
                                            placeholder="Search" name="cat_search" value="{{ request('cat_search') }}">
                                        <button class="btn btn-outline-primary px-3" type="submit"
                                            id="inputGroupFileAddon04"><i class="bi bi-search"></i></button>
                                    </div>
                                </form>
                                <div class="d-flex justify-content-end">
                                    <a href="{{ route('chapter.index', $course->id) }}" class="px-3">
                                        <i class="bi bi-arrow-counterclockwise"></i> Reset
                                    </a>
                                </div>
                            </div>
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th><strong>#</strong></th>
                                            <th><strong>Chapter Title</strong></th>
                                            <th><strong>Sequence</strong></th>
                                            <th><strong>Number of Contents</strong></th>
                                            <th><strong>Action</strong></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($chapters as $chapter)
                                            <tr>
                                                <td class="tableId">{{ $loop->iteration }}</td>
                                                <td class="tableId">
                                                    @if (strlen($chapter->title) > 50)
                                                        {{ substr($chapter->title, 0, 50) . '...' }}
                                                    @else
                                                        {{ $chapter->title }}
                                                    @endif
                                                </td>
                                                <td class="tableId">{{ $chapter->serial_number }}</td>
                                                <td class="tableId">{{ $chapter->contents->count() }}</td>
                                                <td class="tableAction">
                                                    <div class="action-icon">
                                                        <a data-bs-toggle="tooltip" data-bs-placement="top"
                                                            data-bs-custom-class="custom-tooltip"
                                                            data-bs-title="Edit Chapter"
                                                            href="{{ route('chapter.edit', $chapter->id) }}">
                                                            <img src="{{ asset('assets/images/icon/edit.svg') }}"
                                                                alt="icon">
                                                        </a>
                                                        <a data-bs-toggle="tooltip" data-bs-placement="top"
                                                            data-bs-custom-class="custom-tooltip"
                                                            data-bs-title="Delete Chapter" href="#"
                                                            onclick="deleteAction('{{ route('chapter.destroy', $chapter->id) }}')">
                                                            <img src="{{ asset('assets/images/icon/trash.svg') }}"
                                                                alt="icon">
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5">
                                                    <h5 class="text-danger text-center m-0">No Chapter Available</h5>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            {{ $chapters->links() }}
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
        let successMessage = localStorage.getItem('chapterSuccess');
        if (successMessage) {
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
                title: `${successMessage}`,
            });

            localStorage.removeItem('chapterSuccess');
        }
    </script>
@endpush
