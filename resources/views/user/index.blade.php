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
                        <li class="breadcrumb-item active" aria-current="page">{{ __('User') }}</li>
                    </ol>
                </nav>
                <div class="ms-auto">
                    @if (Auth::user()->hasRole('admin') && Auth::user()->is_admin)
                        <a href="{{ route('user.create') }}" class="btn btn-shadow btn-outline-primary mr-3 ms-auto">
                            {{ __('+ New Administrator / Student') }}
                        </a>
                    @else
                        <a href="{{ route('user.create') }}" class="btn btn-shadow btn-outline-primary mr-3 ms-auto">
                            {{ __('+ New Student') }}
                        </a>
                    @endif
                </div>
            </div>

            <div class="row" id="deleteTableItem">
                <div class="col-md-12">
                    <div class="card mb-5">
                        <div class="card-body">
                            <div class=" d-flex justify-content-end mb-3 align-items-center">
                                <form action="{{ route('user.index') }}" method="GET" class="w-25 me-2">
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
                                </div>
                            </div>
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th><strong>#</strong></th>
                                            <th><strong>{{ __('ID') }}</strong></th>
                                            <th><strong>{{ __('Image') }}</strong></th>
                                            <th><strong>{{ __('Name') }}</strong></th>
                                            <th><strong>{{ __('Email') }}</strong></th>
                                            <th><strong>{{ __('Phone') }}</strong></th>
                                            <th><strong>{{ __('Account Verified') }}</strong></th>
                                            <th><strong>{{ __('Status') }}</strong></th>
                                            @if (Auth::user()->hasRole('admin'))
                                                <th><strong>{{ __('Action') }}</strong></th>
                                            @endif
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($users as $user)
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>#{{ $user->id }}</td>
                                                <td class="tableId">
                                                    <div class="d-flex justify-content-start align-items-center"
                                                        style="width: 80px; height: 80px">
                                                        <img src="{{ $user->profilePicturePath }}"
                                                            class="w-100 h-100 object-fit-cover rounded-circle">
                                                    </div>
                                                </td>
                                                <td class="tableProduct">
                                                    <div class="product-pera">
                                                        <p class="priceDis">{{ $user->name }}</p>
                                                    </div>
                                                </td>
                                                <td class="tableId">{{ $user->email }}</td>
                                                <td class="tableId">{{ $user->phone }}</td>
                                                <td class="tableCustomar">
                                                    @if ($user->email_verified_at || $user->is_active)
                                                        <span
                                                            class="badge rounded-pill text-bg-success">{{ __('Yes') }}</span>
                                                    @else
                                                        <span
                                                            class="badge rounded-pill text-bg-danger">{{ __('No') }}</span>
                                                    @endif
                                                </td>
                                                <td class="tableStatus">
                                                    @if ($user->trashed())
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
                                                @if (Auth::user()->hasRole('admin'))
                                                    <td class="tableAction">
                                                        <div class="action-icon">
                                                            @if ($user->trashed())
                                                                <a class="circleIcon" data-bs-toggle="tooltip"
                                                                    data-bs-placement="top"
                                                                    data-bs-custom-class="custom-tooltip"
                                                                    data-bs-title="{{ __('Restore User') }}"
                                                                    href="{{ route('user.restore', $user->id) }}">
                                                                    <img src="{{ asset('assets/images/icon/rotate-left.svg') }}"
                                                                        alt="icon">
                                                                </a>
                                                            @else
                                                                <a class="circleIcon" data-bs-toggle="tooltip"
                                                                    data-bs-placement="top"
                                                                    data-bs-custom-class="custom-tooltip"
                                                                    data-bs-title="{{ __('Edit User') }}"
                                                                    href="{{ route('user.edit', $user->id) }}">
                                                                    <img src="{{ asset('assets/images/icon/edit.svg') }}"
                                                                        alt="icon">
                                                                </a>
                                                                @if ($user->id != Auth::user()->id && !$user->is_admin)
                                                                    <a class="circleIcon" data-bs-toggle="tooltip"
                                                                        data-bs-placement="top"
                                                                        data-bs-custom-class="custom-tooltip"
                                                                        data-bs-title="Suspend User" href="#"
                                                                        onclick="deleteAction('{{ route('user.destroy', $user->id) }}')">
                                                                        <img src="{{ asset('assets/images/icon/user-ban.svg') }}"
                                                                            alt="icon">
                                                                    </a>
                                                                @endif

                                                                @if (!$user->is_admin && !$user->instructor)
                                                                    <a class="circleIcon" data-bs-toggle="tooltip"
                                                                        data-bs-placement="top"
                                                                        data-bs-custom-class="custom-tooltip"
                                                                        data-bs-title="{{ __('Promote to Instructor') }}"
                                                                        href="{{ route('instructor.promote', $user->id) }}">
                                                                        <i
                                                                            class="bi bi-person-fill-up circleIcon text-dark"></i>
                                                                    </a>
                                                                @endif
                                                            @endif
                                                        </div>
                                                    </td>
                                                @endif
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="9">
                                                    <h5 class="text-danger text-center m-0">{{ __('No User Available') }}
                                                    </h5>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            {{ $users->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ****End-Body-Section**** -->
    </div>
@endsection
