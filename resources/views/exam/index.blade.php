@extends('layouts.app')

@section('title', $app_setting['name'] . ' | '.__('Exam List'))

@section('content')
    <!-- ****Body-Section***** -->
    <div class="app-main-outer">
        <div class="app-main-inner">
            <div
                class="page-title-actions px-3 py-3 d-flex justify-content-between align-items-center bg-white rounded mb-3">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb m-0 p-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }}</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('course.index') }}">{{ __('Course') }}</a></li>
                        <li class="breadcrumb-item active" aria-current="page">{{ __('Exam') }}</li>
                    </ol>
                </nav>
            </div>

            <div class="mb-3 bg-primary-light text-dark p-4 rounded">
                <div class="row align-items-center">
                    <div class="col-md-6 text-wrap">
                        <h4 class="m-0">{{ __('Showing chapters for') }}: <strong>{{ $course->title }}</strong></h4>
                    </div>
                    <div class="col-md-6">
                        <div class="d-flex justify-content-end">
                            <a href="{{ route('exam.create', $course->id) }}"
                                class="btn btn-shadow btn-outline-primary mr-3 ms-auto">
                                {{ __('+ New Exam') }}
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
                                <form action="{{ route('exam.index', $course->id) }}" method="GET" class="w-25 me-2">
                                    <div class="input-group">
                                        <input type="text" class="form-control" id="inputGroupFile04"
                                            aria-describedby="inputGroupFileAddon04" aria-label="Upload"
                                            placeholder="{{ __('Search') }}" name="cat_search" value="{{ request('cat_search') }}">
                                        <button class="btn btn-outline-primary px-3" type="submit"
                                            id="inputGroupFileAddon04"><i class="bi bi-search"></i></button>
                                    </div>
                                </form>
                                <div class="d-flex justify-content-end">
                                    <a href="{{ route('exam.index', $course->id) }}" class="px-3">
                                        <i class="bi bi-arrow-counterclockwise"></i> {{ __('Reset') }}
                                    </a>
                                </div>
                            </div>
                            <div class="table-responsive-lg">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th><strong>#</strong></th>
                                            <th><strong>{{ __('Exam Title') }}</strong></th>
                                            <th><strong>{{ __('Total Questions') }}</strong></th>
                                            <th><strong>{{ __('Duration') }}</strong></th>
                                            <th><strong>{{ __('Mark Per Question') }}</strong></th>
                                            <th><strong>{{ __('Pass Marks') }}</strong></th>
                                            <th><strong>{{ __('Action') }}</strong></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($exams as $exam)
                                            <tr>
                                                <td class="tableId">{{ $loop->iteration }}</td>
                                                <td class="tableId">
                                                    @if (strlen($exam->title) > 50)
                                                        {{ substr($exam->title, 0, 50) . '...' }}
                                                    @else
                                                        {{ $exam->title }}
                                                    @endif
                                                </td>
                                                <td class="tableId">{{ $exam->questions->count() }}</td>
                                                <td class="tableId">{{ $exam->duration }}</td>
                                                <td class="tableId">{{ $exam->mark_per_question }}</td>
                                                <td class="tableId">{{ $exam->pass_marks }}</td>
                                                <td class="tableAction">
                                                    <div class="action-icon">
                                                        <a data-bs-toggle="tooltip" data-bs-placement="top"
                                                            data-bs-custom-class="custom-tooltip" data-bs-title="{{ __('Edit Exam') }}"
                                                            href="{{ route('exam.edit', $exam->id) }}">
                                                            <img src="{{ asset('assets/images/icon/edit.svg') }}"
                                                                alt="icon">
                                                        </a>
                                                        <a data-bs-toggle="tooltip" data-bs-placement="top"
                                                            data-bs-custom-class="custom-tooltip"
                                                            data-bs-title="{{ __('Delete Exam') }}" href="#"
                                                            onclick="deleteAction('{{ route('exam.destroy', $exam->id) }}')">
                                                            <img src="{{ asset('assets/images/icon/trash.svg') }}"
                                                                alt="icon">
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="7">
                                                    <h5 class="text-danger text-center m-0">{{ __('No Exam Available') }}</h5>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            {{ $exams->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ****End-Body-Section**** -->
    </div>
@endsection
