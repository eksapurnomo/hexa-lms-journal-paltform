@extends('layouts.app')

@section('title', $app_setting['name'] . ' | ' . __('User List'))

@section('content')
    <!-- ****Body-Section***** -->
    <div class="app-main-outer">
        <div class="app-main-inner">
            <div
                class="page-title-actions px-3 py-3 d-flex justify-content-between align-items-center bg-white rounded mb-3">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb m-0 p-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }}</a></li>
                        <li class="breadcrumb-item active" aria-current="page">{{ __('Newslatter Subscribers') }}</li>
                    </ol>
                </nav>
            </div>

            <div class="row" id="deleteTableItem">
                <div class="col-md-12">
                    <div class="card mb-5">
                        <div class="card-body">
                            <div class=" d-flex justify-content-end mb-3 align-items-center">
                                {{-- <form action="{{ route('user.index') }}" method="GET" class="w-25 me-2">
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
                                    <a href="{{ route('user.index') }}" class="px-3">
                                        <i class="bi bi-arrow-counterclockwise"></i> {{ __('Reset') }}
                                    </a>
                                </div> --}}
                            </div>
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th><strong>#</strong></th>
                                            <th><strong>{{ __('ID') }}</strong></th>
                                            <th><strong>{{ __('Email') }}</strong></th>
                                            <th><strong>{{ __('Status') }}</strong></th>
                                            @if (Auth::user()->hasRole('admin'))
                                                <th><strong>{{ __('Action') }}</strong></th>
                                            @endif
                                        </tr>
                                    </thead>

                                    <tbody>
                                        @forelse ($subscribers as $subscribe)
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td># {{ $subscribe->id }} {{ config('app.name') }}</td>
                                                <td class="tableId">{{ $subscribe->email }}</td>

                                                <td class="tableStatus">
                                                    @if ($subscribe->status == 0)
                                                        <div class="statusItem">
                                                            <div class="circleDot animatedPending"></div>
                                                            <div class="statusText">
                                                                <span class="stutsPanding">{{ __('Pending') }}</span>
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
                                                @if (Auth::user()->hasRole('admin'))
                                                    <td class="tableAction">
                                                        <div class="action-icon">
                                                            @if ($subscribe->trashed())
                                                                <a class="circleIcon" data-bs-toggle="tooltip"
                                                                    data-bs-placement="top"
                                                                    data-bs-custom-class="custom-tooltip"
                                                                    data-bs-title="{{ __('Restore Subscriber') }}"
                                                                    href="{{ route('newslatter.restore', $subscribe->id) }}">
                                                                    <img src="{{ asset('assets/images/icon/rotate-left.svg') }}"
                                                                        alt="icon">
                                                                </a>
                                                            @else
                                                                <a class="circleIcon" data-bs-toggle="tooltip"
                                                                    data-bs-placement="top"
                                                                    data-bs-custom-class="custom-tooltip"
                                                                    data-bs-title="{{ __('Delete Subscriber') }}"
                                                                    href="{{ route('newslatter.delete', $subscribe->id) }}">
                                                                    <img src="{{ asset('assets/images/icon/trash.svg') }}"
                                                                        alt="icon">
                                                                </a>
                                                                <a class="circleIcon" data-bs-toggle="tooltip"
                                                                    data-bs-placement="top"
                                                                    data-bs-custom-class="custom-tooltip"
                                                                    data-bs-title="{{ __('Send Welcome Mail') }}"
                                                                    href="{{ route('newslatter.send.mail', $subscribe->id) }}">
                                                                    <i class="fa-solid fa-paper-plane"></i>
                                                                </a>
                                                            @endif
                                                        </div>
                                                    </td>
                                                @endif
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="9">
                                                    <h5 class="text-danger text-center m-0">{{ __('No Subscriber Available') }}
                                                    </h5>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            {{ $subscribers->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ****End-Body-Section**** -->
    </div>
@endsection
