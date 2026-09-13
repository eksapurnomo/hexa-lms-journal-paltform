@extends('layouts.app')

@section('title', $app_setting['name'] . ' | '.__('User Role List'))

@section('content')
    <!-- ****Body-Section***** -->
    <div class="app-main-outer">
        <div class="app-main-inner">
            <div class="page-title-actions px-3 py-3 mb-3">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb m-0 p-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }}</a></li>
                        <li class="breadcrumb-item active" aria-current="page">{{ __('Role Management') }}</li>
                    </ol>
                </nav>
            </div>

            <div class="row">
                <div class="col-md-12 mb-2">
                    <div class="card">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-md-6">
                                    <h3 class="m-0 p-0 ">{{ __('User Role Management') }}</h3>
                                </div>
                                <div class="col-md-6 d-flex justify-content-end">
                                    @can('role.create')
                                        <div class="ms-auto">
                                            <a href="{{ route('role.create') }}"
                                                class="btn btn-shadow btn-outline-primary mr-3 ms-auto">
                                                {{ __('+ New Role') }}
                                            </a>
                                        </div>
                                    @endcan
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row" id="deleteTableItem">
                <div class="col-md-12">
                    <div class="card mb-5">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="dataTable" class="table">
                                    <thead>
                                        <tr>
                                            <th><strong>#</strong></th>
                                            <th><strong>{{ __('User Name') }}</strong></th>
                                            <th><strong>{{ __('Email') }}</strong></th>
                                            <th><strong>{{ __('Phone') }}</strong></th>
                                            <th><strong>{{ __('Role') }}</strong></th>
                                            <th><strong>{{ __('Status') }}</strong></th>
                                            @can('role.assign_roletouser')
                                                <th><strong>{{ __('Action') }}</strong></th>
                                            @endcan
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($users as $user)
                                            <tr>
                                                <td class="tableId">{{ $loop->iteration }}</td>
                                                <td class="tableProduct">
                                                    <div class="listproduct-section">
                                                        <div class="listproducts-image">
                                                            <img src="{{ $user->profilePicturePath }}">
                                                        </div>
                                                        <div class="product-pera">
                                                            <p class="priceDis">{{ $user->name }}</p>
                                                        </div>
                                                    </div>
                                                </td>

                                                <td class="tableId">{{ $user->email }}</td>
                                                <td class="tableId">{{ $user->phone }}</td>
                                                <td class="tableId pe-auto">
                                                    @if ($user?->roles->isNotEmpty())
                                                        @foreach ($user->roles as $role)
                                                            <div class="position-relative">
                                                                <span class="badge bg-primary">{{ $role->name }}</span>
                                                                @can('role.dispatchRole')
                                                                    <a href="{{ route('role.dispatchRole', ['user' => $user->id, 'role' => $role->id]) }}"
                                                                        class="position-absolute top-0 end-25 translate-middle badge rounded-circle bg-danger">
                                                                        <i class="fa-solid fa-xmark"></i>
                                                                    </a>
                                                                @endcan
                                                            </div>
                                                        @endforeach
                                                    @else
                                                        N/A
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
                                                @can('role.assign_roletouser')
                                                    <td class="tableAction">
                                                        <div class="action-icon">
                                                            <button type="button"
                                                                data-bs-target="#assignRole{{ $user->id }}"
                                                                class="circleIcon" data-bs-toggle="modal"
                                                                data-bs-placement="top" data-bs-custom-class="custom-tooltip"
                                                                data-bs-title="{{ __('Assign Role') }}">
                                                                <img src="{{ asset('assets/images/icon/edit.svg') }}"
                                                                    alt="icon">
                                                            </button>
                                                        </div>
                                                    </td>
                                                @endcan
                                            </tr>


                                            {{-- Role ASSIGN Modal --}}
                                            <div class="modal fade" id="assignRole{{ $user->id }}" tabindex="-1"
                                                aria-labelledby="exampleModalLabel" aria-hidden="true">
                                                <div class="modal-dialog modal-dialog-centered">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h1 class="modal-title fs-5" id="examproleEditleModalLabel">
                                                                {{ __('Edit Role') }}</h1>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                                aria-label="Close"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <form action="{{ route('role.assign_roletouser', $user->id) }}"
                                                                method="POST">
                                                                @csrf
                                                                <div
                                                                    class="d-flex align-items-center flex-wrap gap-3 justify-content-between my-3">
                                                                    <div class="position-relative flex-grow-1">
                                                                        <select class="form-select py-3" name="role_name"
                                                                            id="role_name">
                                                                            <option value="">{{ __('Select Role') }}</option>
                                                                            @foreach ($roles as $role)
                                                                                <option value="{{ $role->name }}">
                                                                                    {{ $role->name }}</option>
                                                                            @endforeach
                                                                        </select>
                                                                    </div>
                                                                    <button type="submit" class="btn btn-primary py-3">
                                                                        <i class="fa fa-plus"></i>
                                                                        {{ __('Assign Role') }}
                                                                    </button>
                                                                </div>
                                                            </form>
                                                        </div>

                                                    </div>
                                                </div>
                                            </div>
                                            {{-- Role ASSIGN Modal --}}
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ****End-Body-Section**** -->
    </div>
@endsection
