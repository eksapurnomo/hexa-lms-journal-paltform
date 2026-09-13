@extends('layouts.app')

@section('title', $app_setting['name'] . ' | ' . __('User Create'))

@section('content')
    <!-- ****Body-Section***** -->
    <div class="app-main-outer">
        <div class="app-main-inner">
            <div class="page-title-actions px-3 d-flex">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }}</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('user.index') }}">{{ __('User') }}</a></li>
                        <li class="breadcrumb-item active" aria-current="page">{{ __('Create') }}</li>
                    </ol>
                </nav>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-body">
                            <h3 class="m-0 p-0">
                                {{ __('Create a New Student / User') }}</h3>
                        </div>
                    </div>
                </div>
            </div>


            <form action="{{ route('user.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="row">
                    <div class="col-md-12 my-3">
                        <div class="card">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="row">
                                            <div class="col-md-12 mb-3">
                                                <label class="form-label">{{ __('Name') }} <span
                                                        class="text-danger">*</span></label>
                                                <input type="text" name="name" class="form-control"
                                                    placeholder="{{ __('Enter user name') }}" value="{{ old('name') }}">
                                                @error('name')
                                                    <p class="text-danger my-2">{{ $message }}</p>
                                                @enderror
                                            </div>
                                            <div class="col-md-12 mb-3">
                                                <label class="form-label">{{ __('Email') }} <span
                                                        class="text-danger">*</span></label>
                                                <input type="email" name="email" class="form-control"
                                                    placeholder="{{ __('Enter user email') }}"
                                                    value="{{ old('email') }}">
                                                @error('email')
                                                    <p class="text-danger my-2">{{ $message }}</p>
                                                @enderror
                                            </div>
                                            <div class="col-md-12 mb-3">
                                                <label class="form-label">{{ __('Phone') }} <span
                                                        class="text-danger">*</span></label>
                                                <input type="text" name="phone" class="form-control"
                                                    placeholder="{{ __('Enter user phone') }}"
                                                    value="{{ old('phone') }}">

                                                @error('phone')
                                                    <p class="text-danger my-2">{{ $message }}</p>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="mb-3 d-flex justify-content-center">
                                                    <div style="width: 150px; height:150px; border-radius: 50%;">
                                                        <img id="courseImagePreview" src="/assets/images/media/teacher.jpg"
                                                            class="w-100 h-100"
                                                            style="border-radius:50%; object-fit: cover">
                                                    </div>
                                                </div>
                                                <h4 class="form-label">{{ __('Profile Picture') }} (JPG, JPEG, PNG)*</h4>
                                                <label for="formFileImage" class="w-100 border rounded-3">
                                                    <div class="d-flex justify-content-center align-items-center gap-2 p-3"
                                                        style="width: 160px; background-color: #EDEEF1">
                                                        <span>{{ __('Choose a file') }}</span>
                                                        <img src="/assets/images/media/file-plus.svg">
                                                    </div>
                                                </label>
                                                <input name="profile_picture" class="form-control form-control-lg"
                                                    id="formFileImage" type="file" hidden
                                                    onchange="document.getElementById('courseImagePreview').src = window.URL.createObjectURL(this.files[0])" />
                                            </div>
                                            @error('profile_picture')
                                                <span class="text-danger mt-2">{{ $message }}</span>
                                            @enderror

                                        </div>
                                    </div>
                                    <div class="col-md-12 mt-5">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <label class="form-label">{{ __('Password') }} <span
                                                        class="text-danger">*</span></label>
                                                <input type="password" name="password" class="form-control"
                                                    placeholder="{{ __('Enter user password') }}">
                                                @error('password')
                                                    <p class="text-danger my-2">{{ $message }}</p>
                                                @enderror
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">{{ __('Confirm Password') }}</label>
                                                <input type="password" name="password_confirmation"
                                                    class="form-control @if (strpos($errors->first('password'), 'The password field confirmation does not match') !== false) is-invalid @endif"
                                                    placeholder="{{ __('Enter user password again') }}">
                                                @error('password_confirmation')
                                                    <p class="text-danger my-2">{{ $message }}</p>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12 mb-5">
                        <div class="card">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col-md-6 d-flex align-items-center gap-3">
                                        <div class="form-check">
                                            <input id="activeInput" name="is_active" class="form-check-input"
                                                type="checkbox">
                                            <label for="activeInput" class="form-check-label">
                                                {{ __('Verify Account by Default') }}
                                            </label>
                                        </div>
                                        <div class="form-check">
                                            <input id="adminInput" name="is_admin" class="form-check-input"
                                                type="checkbox">
                                            <label for="adminInput" class="form-check-label">
                                                {{ __('Allow Admin Privileges') }}
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-6 d-flex justify-content-end">
                                        <button type="submit"
                                            class="btn btn-primary px-5 py-2">{{ __('Create') }}</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </form>

            <!-- ****End-Body-Section**** -->
        </div>
    </div>
@endsection
