@extends('layouts.app')

@section('title', $app_setting['name'] . ' | '.__('User Role Create'))


@section('content')
    <!-- ****Body-Section***** -->
    <div class="app-main-outer">
        <div class="app-main-inner">

            <div class="row">
                <div class="col-md-12 mb-2">
                    <div class="card">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-md-12">
                                    <h3 class="m-0 p-0 ">{{ __('Create a New Role') }}</h3>
                                </div>
                                {{-- <div class="col-md-6 d-flex justify-content-end">
                            <div class="border border-primary rounded p-2">
                                <a href="#" class="btn-shadow mr-3 ms-auto text-primary">
                                    + New Role Assign
                                </a>
                            </div>
                        </div> --}}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <style>
                .search-icon {
                    position: absolute;
                    top: 50%;
                    right: 15px;
                    transform: translateY(-50%);
                }
            </style>

            <div class="row">
                <div class="col-md-7">
                    <div class="card">
                        <div class="card-body">

                            <h4>{{ __('Roles') }}</h4>
                            <form action="{{ route('role.store') }}" method="POST" class="pb-3">
                                @csrf
                                <div class="d-flex align-items-center flex-wrap gap-3 justify-content-between mt-3">
                                    <div class="position-relative flex-grow-1">
                                        <input type="text" class="form-control py-2.5" name="role_title"
                                            placeholder="{{ __('create new role') }}" id="search" value="{{ old('role_title') }}">
                                        <span class="search-icon">
                                            <i class="fa fa-plus"></i>
                                        </span>
                                    </div>
                                    <button type="submit" class="btn btn-primary py-3">
                                        <i class="fa fa-plus"></i>
                                        {{ __('Create Role') }}
                                    </button>
                                </div>
                            </form>
                            @forelse ($roles as $role)
                                <div
                                    class="infocard mt-3 mb-1 d-flex justify-content-between align-items-center border rounded p-2">
                                    <a href="{{ route('role.get_permission', $role->id) }}"
                                        class="d-flex align-items-center gap-2">
                                        <div class="infocard-image p-2">
                                            <img src="{{ $role->name == 'admin' ? asset('assets/images/menu/award.svg') : asset('assets/images/menu/user-check.svg') }}"
                                                alt="avatar">
                                        </div>
                                        <div class="infodescription">
                                            <div class="infocard-name text-capitalize">{{ $role->name }}</div>
                                        </div>
                                    </a>
                                    <div>
                                        @if ($role->name !== 'admin' && $role->name !== 'instructor')
                                            <div class="icons d-flex justify-content-between align-items-center">
                                                <button type="button" class="bg-transparent border-0"
                                                    data-bs-toggle="modal" data-bs-target="#roleEdit{{ $role->id }}">
                                                    <img src="{{ asset('assets/images/icon/edit.svg') }}" alt="icon">
                                                </button>
                                                <a href="{{ route('role.delete', $role->id) }}"><img
                                                        src="{{ asset('assets/images/icon/trash.svg') }}"
                                                        alt="icon"></a>
                                            </div>
                                        @else
                                            <div class="icons d-flex justify-content-between align-items-center">
                                                <p class="badge bg-warning">
                                                    {{ __('Sorry, the admin role cannot be modified or deleted by anyone') }}.
                                                </p>
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                {{-- Role Edit Modal --}}
                                <div class="modal fade" id="roleEdit{{ $role->id }}" tabindex="-1"
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
                                                <form action="{{ route('role.update', $role->id) }}" method="POST">
                                                    @csrf
                                                    <div
                                                        class="d-flex align-items-center flex-wrap gap-3 justify-content-between my-3">
                                                        <div class="position-relative flex-grow-1">
                                                            <input type="text" class="form-control py-2.5"
                                                                name="role_title" placeholder="{{ __('Search by role name') }}"
                                                                id="search" value="{{ $role->name }}">
                                                            <span class="search-icon">
                                                                <i class="fa fa-search"></i>
                                                            </span>
                                                        </div>
                                                        <button type="submit" class="btn btn-primary py-3">
                                                            <i class="fa fa-plus"></i>
                                                            {{ __('Update Role') }}
                                                        </button>
                                                    </div>
                                                </form>
                                            </div>

                                        </div>
                                    </div>
                                </div>
                                {{-- Role Edit Modal --}}
                            @empty
                                <p class="text-center text-danger my-5 text-uppercase">{{ __('No Role Found') }}</p>
                            @endforelse
                        </div>
                    </div>
                </div>
                <div class="col-lg-5 colRight d-flex flex-column">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="mb-0">{{ __('Permissions') }}</h4>

                            <div class="mt-3 permission-container flex-grow-1">
                                <div
                                    class="d-flex align-items-center justify-content-between flex-wrap gap-2 border-bottom pb-2">
                                    <div class="d-flex align-items-center gap-2">
                                        <input type="checkbox" id="checkAll" class="form-check-input m-0"
                                            style="width: 20px; height: 20px">
                                        <span class="text-capitalize fz-18">
                                            <span id="showTotalPermission"></span>
                                            {{ !empty($permissions) ? __('Select All') : __('No Permissions Available') }}

                                        </span>
                                    </div>
                                    <span type="button" class="text-danger cursor-pointer" id="uncheckAll">
                                        {{ __('Clear') }}
                                    </span>
                                </div>
                                @php
                                    $roleId = isset($roleId) ? $roleId : '';
                                @endphp
                                @if (request()->routeIs('role.get_permission'))
                                    <form action="{{ route('role.assign_roletopermission', $roleId) }}" method="POST">
                                        @csrf
                                        @if (!empty($permissions))
                                            @foreach ($permissions as $subject => $allPermission)
                                                <div class="d-flex flex-column gap-3 mt-3">
                                                    <div>
                                                        <p class="text-capitalize m-0 fz-20 pb-1 fw-bold">
                                                            {{ $subject }}
                                                        </p>
                                                        <div class="fz-18 d-flex align-items-center flex-wrap gap-3">
                                                            @foreach ($allPermission as $selectedPermission)
                                                                <div class="d-flex align-items-center gap-2">
                                                                    <input
                                                                        id="permissionChecked{{ $subject . '.' . $selectedPermission }}"
                                                                        type="checkbox" class="form-check-input m-0"
                                                                        style="width: 18px; height: 18px"
                                                                        name="permissions[]"
                                                                        value="{{ $subject . '.' . $selectedPermission }}"
                                                                        {{ $activePermission = in_array($subject . '.' . $selectedPermission, $rolePermission) ? 'checked' : '' }}>
                                                                    <label
                                                                        for="permissionChecked{{ $subject . '.' . $selectedPermission }}"
                                                                        class="m-0">{{ filterPermission($selectedPermission) }}</label>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                            @endforeach
                                        @else
                                            <p class="text-center text-danger my-5 text-uppercase">
                                                {{ __('No Permission Found') }}
                                            </p>
                                        @endif

                                        <div class="mt-3">
                                            <button type="submit"
                                                class="border border-2 border-primary bg-transparent text-primary py-2.5 px-4">
                                                <i class="fa-solid fa-arrows-rotate"></i> {{ __('Update') }}
                                            </button>
                                        </div>
                            </div>
                            </form>
                        @else
                            <div class="d-flex align-items-center justify-content-center flex-column h-100 mt-3">
                                <div class="fs-1 text-secondary">
                                    <i class="fa-solid fa-user-lock"></i>
                                </div>
                                <span class="text-capitalize fz-22 fst-italic">
                                    {{ __('No Permissions Available') }}
                                </span>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>


    </div>
    </div>

@endsection


@push('scripts')
    <script>
        $('#checkAll').click(function(event) {
            if (this.checked) {
                // Iterate each checkbox
                $(':checkbox').each(function() {
                    this.checked = true;
                });
            } else {
                $(':checkbox').each(function() {
                    this.checked = false;
                });
            }
        });

        $('#uncheckAll').click(function(event) {
            if (this.checked) {
                // Iterate each checkbox
                $(':checkbox').each(function() {
                    this.checked = true;
                });
            } else {
                $(':checkbox').each(function() {
                    this.checked = false;
                });
            }
        });
    </script>


    @if (session('title_exists'))
        <script>
            const Toast = Swal.mixin({
                toast: true,
                position: "top-end",
                showConfirmButton: false,
                timer: 3500,
                timerProgressBar: true,
                didOpen: (toast) => {
                    toast.onmouseenter = Swal.stopTimer;
                    toast.onmouseleave = Swal.resumeTimer;
                }
            });
            Toast.fire({
                icon: "error",
                title: "{{ session('title_exists') }}"
            });
        </script>
    @endif
@endpush
