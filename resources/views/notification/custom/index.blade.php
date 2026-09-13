@extends('layouts.app')

@section('title', $app_setting['name'] . ' | ' . __('Custom Notification List'))

@section('content')
    <!-- ****Body-Section***** -->
    <div class="app-main-outer">
        <div class="app-main-inner">
            {{-- breadcrumb --}}
            <div class="page-title-actions px-3 d-flex">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }}</a></li>
                        <li class="breadcrumb-item active" aria-current="page">{{ __('Custom Notification') }}</li>
                    </ol>
                </nav>
            </div>
            {{-- breadcrumb --}}

            <form action="{{ route('notification.custom.send.message') }}" method="POST">
                @csrf
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card mb-3">
                            <div class="card-header my-2">
                                <h3 class="m-0 p-0"><i class="bi bi-chat-text"></i> {{ __('Send Notification') }}</h3>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <p class="fw-bold mb-2 text-primary">{{ __('Edit Notification') }}</p>
                                    <ul>
                                        {{-- <li class="text-muted">Remember to include <span
                                                class="fw-bold bg-light mx-1">{user_name}</span> where
                                            the user’s name should appear. It will automatically be replaced.</li> --}}
                                        <li class="text-muted">{{ __('You can use') }} <span class="fw-bold bg-light mx-1">
                                                {user_name}</span>
                                            {{ __('to include the users name. This is optional but helpful for personalization') }}.
                                        </li>
                                    </ul>
                                </div>
                                <div class="mb-3">
                                    <label for="exampleInputEmail1" class="form-label">{{ __('Title') }}</label>
                                    <input type="text" class="form-control" id="exampleInputEmail1"
                                        aria-describedby="emailHelp" placeholder="{{ __('Notification Title') }}"
                                        name="title" value="{{ old('title') }}">
                                    @error('title')
                                        <div id="emailHelp" class="form-text text-danger">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                                <div class="mb-3">
                                    <label for="exampleInputPassword1" class="form-label">{{ __('Message') }}</label>
                                    <textarea name="message" placeholder="{{ __('Notification Message') }}" rows="5" class="form-control"
                                        id="exampleInputPassword1">{{ old('message') }}</textarea>
                                    @error('message')
                                        <div id="emailHelp" class="form-text text-danger">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                                <div class="mb-3 form-check">
                                    <input type="checkbox"
                                        class="form-check-input {{ session('confirmation_not_enabled') ? 'is-invalid' : '' }}"
                                        id="exampleCheck1" name="confirm">
                                    <label class="form-check-label"
                                        for="exampleCheck1">{{ __('Are you confirm') }}?</label>
                                </div>
                                <div class="d-flex justify-content-end">
                                    <button type="submit"
                                        class="btn btn-primary px-5 py-2">{{ __('Send Message') }}</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-12">
                        <div class="card mt-3">
                            <div class="card-body">

                                <div class="d-flex justify-content-end align-items-end flex-wrap mb-3" style="gap: 10px">
                                    <div style="width: 200px">
                                        <label class="font-weight-normal font-14 mb-2">
                                            {{ __('Filter by Device Type') }}
                                        </label>

                                        <select id="deviceType" class="form-control" name="user_scope_filter"
                                            onchange="scopeFilter(event)">
                                            <option value="none">----{{ __('Select Option') }}----</option>
                                            <option {{ request('user_scope_filter') == 'all' ? 'selected' : '' }}
                                                value="all">
                                                {{ __('All') }}
                                            </option>
                                            <option {{ request('user_scope_filter') == 'instructor' ? 'selected' : '' }}
                                                value="instructor">
                                                {{ __('Instructors') }}
                                            </option>
                                            <option {{ request('user_scope_filter') == 'student' ? 'selected' : '' }}
                                                value="student">
                                                {{ __('Students') }}
                                            </option>
                                        </select>

                                    </div>

                                </div>

                                @error('users')
                                    <div id="emailHelp" class="form-text text-danger my-2">
                                        {{ $message }}
                                    </div>
                                @enderror
                                <div class="table-responsive maxScroll mt-2">
                                    <table id="dataTable" class="table table-bordered table-striped" id="myTable"
                                        style="overflow: auto;">
                                        <thead>
                                            <tr>
                                                <th class="px-0 text-center" style="width: 42px">
                                                    <input type="checkbox" onclick="toggleAll(this);">
                                                </th>
                                                <th>{{ __('Thumbnail') }}</th>
                                                <th>{{ __('Name') }}</th>
                                                <th>{{ __('Email Address') }}</th>
                                                <th>{{ __('Phone Number') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody id="notificationUsers">
                                            @forelse ($users as $user)
                                                <tr>
                                                    <td class="py-2 px-0 text-center">
                                                        <input type="checkbox" name="users[]" value="{{ $user->id }}">
                                                    </td>
                                                    <td>
                                                        <img src="{{ $user->profilePicturePath }}" alt="profile picture"
                                                            width="40" height="40" loading="lazy" class="rounded">
                                                    </td>
                                                    <td class="py-2">{{ $user->name }}</td>
                                                    <td>{{ $user->email }}</td>
                                                    <td>{{ $user->phone }}</td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="5" class="text-center text-danger">
                                                        {{ __('No User Found') }}</td>
                                                </tr>
                                            @endforelse

                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>


            </form>


        </div>
    </div>
    <!-- ****End-Body-Section***** -->
@endsection


@push('scripts')
    <script>
        function toggleAll(source) {
            checkboxes = document.querySelectorAll('input[type="checkbox"]');
            for (var i = 0; i < checkboxes.length; i++) {
                if (checkboxes[i] != source)
                    checkboxes[i].checked = source.checked;
            }
        }
    </script>

    <script>
        function scopeFilter(event) {
            let queryValue = event.target.value;
            let url = "{{ route('notification.custom.index') }}?user_scope_filter=" + queryValue;
            window.location.href = url;
        }
    </script>

    @if (session('notification_not_enabled'))
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
                title: "{{ session('notification_not_enabled') }}"
            });
        </script>
    @endif

    @if (session('confirmation_not_enabled'))
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
                title: "{{ session('confirmation_not_enabled') }}"
            });
        </script>
    @endif

@endpush
