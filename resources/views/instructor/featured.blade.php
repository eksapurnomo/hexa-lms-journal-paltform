@extends('layouts.app')

@section('title', $app_setting['name'] . ' | '.__('Featured Instructors'))

@section('content')
    <!-- ****Body-Section***** -->
    <div class="app-main-outer">
        <div class="app-main-inner">
            <div
                class="page-title-actions px-3 py-3 d-flex justify-content-between align-items-center bg-white rounded mb-3">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb m-0 p-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }}</a></li>
                        <li class="breadcrumb-item active" aria-current="page">{{ __('Instructor') }}</li>
                    </ol>
                </nav>
                <div class="d-flex justify-content-end">
                    <a href="{{ route('instructor.create') }}" class="btn btn-shadow btn-outline-primary mr-3 ms-auto">
                        {{ __('+ New Instructor') }}
                    </a>
                </div>
            </div>

            <div class="row" id="deleteTableItem">
                <div class="col-md-12">
                    <div class="card mb-5">
                        <div class="card-body">
                            <div class=" d-flex justify-content-end mb-3 align-items-center">
                                <form action="{{ route('instructor.featured') }}" method="GET" class="w-25 me-2">
                                    <div class="input-group">
                                        <input type="text" class="form-control" id="inputGroupFile04"
                                            aria-describedby="inputGroupFileAddon04" aria-label="Upload"
                                            placeholder="{{ __('Search') }}" name="cat_search" value="{{ request('cat_search') }}">
                                        <button class="btn btn-outline-primary px-3" type="submit"
                                            id="inputGroupFileAddon04"><i class="bi bi-search"></i></button>
                                    </div>
                                </form>
                                <div class="d-flex justify-content-end">
                                    <a href="{{ route('instructor.featured') }}" class="px-3">
                                        <i class="bi bi-arrow-counterclockwise"></i> {{ __('Reset') }}
                                    </a>
                                </div>
                            </div>
                            <div class="table-responsive-lg">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th><strong>#</strong></th>
                                            <th><strong>{{ __('User') }}</strong></th>
                                            <th><strong>{{ __('Email') }}</strong></th>
                                            <th><strong>{{ __('Title') }}</strong></th>
                                            <th><strong>{{ __('Is Featured') }}</strong></th>
                                            <th><strong>{{ __('Status') }}</strong></th>
                                            <th><strong>{{ __('Action') }}</strong></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($instructors as $instructor)
                                            <tr>
                                                <td class="tableId">{{ $loop->iteration }}</td>
                                                <td class="tableProduct">
                                                    <div class="listproduct-section">
                                                        <div class="listproducts-image">
                                                            <img src="{{ $instructor->user->profilePicturePath }}">
                                                        </div>
                                                        <div class="product-pera">
                                                            <p class="priceDis">{{ $instructor->user->name }}</p>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="tableId">{{ $instructor->user->email }}</td>
                                                <td class="tableId">{{ $instructor->title }}</td>
                                                <td class="tableCustomar">
                                                    @if ($instructor->is_featured)
                                                        <span class="badge rounded-pill text-bg-success">{{ __('Yes') }}</span>
                                                    @else
                                                        <span class="badge rounded-pill text-bg-danger">{{ __('No') }}</span>
                                                    @endif
                                                </td>
                                                <td class="tableStatus">
                                                    @if ($instructor->trashed())
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
                                                <td class="tableAction">
                                                    <div class="action-icon">
                                                        @if ($instructor->trashed())
                                                            <a class="circleIcon" data-bs-toggle="tooltip"
                                                                data-bs-placement="top"
                                                                data-bs-custom-class="custom-tooltip"
                                                                data-bs-title="{{ __('Restore Instructor') }}"
                                                                href="{{ route('instructor.restore', $instructor->id) }}"><i
                                                                    class="bi bi-arrow-counterclockwise Circleicon"></i></a>
                                                        @else
                                                            <a class="circleIcon" data-bs-toggle="tooltip"
                                                                data-bs-placement="top"
                                                                data-bs-custom-class="custom-tooltip"
                                                                data-bs-title="{{ __('Edit Instructor') }}"
                                                                href="{{ route('instructor.edit', $instructor->id) }}">
                                                                <img src="{{ asset('assets/images/icon/edit.svg') }}"
                                                                    alt="icon">
                                                            </a>
                                                            @if ($instructor->user_id != auth()->user()->id)
                                                                <a class="circleIcon" data-bs-toggle="tooltip"
                                                                    data-bs-placement="top"
                                                                    data-bs-custom-class="custom-tooltip"
                                                                    data-bs-title="{{ __('Suspend Instructor') }}" href="#"
                                                                    onclick="deleteAction('{{ route('instructor.destroy', $instructor->id) }}')">
                                                                    <img src="{{ asset('assets/images/icon/user-ban.svg') }}"
                                                                        alt="icon">
                                                                </a>
                                                            @endif
                                                        @endif
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="7">
                                                    <h5 class="text-danger text-center m-0">{{ __('No Instructor Available') }}</h5>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            {{ $instructors->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ****End-Body-Section**** -->
    </div>
@endsection
