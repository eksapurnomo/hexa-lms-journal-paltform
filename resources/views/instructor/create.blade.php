@extends('layouts.app')

@section('title', $app_setting['name'] . ' | ' .__('Instructor Create'))

@section('content')
    <!-- ****Body-Section***** -->
    <div class="app-main-outer">
        <div class="app-main-inner">
            <div class="page-title-actions px-3 d-flex">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }}</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('instructor.index') }}">{{ __('Instructor') }}</a></li>
                        <li class="breadcrumb-item active" aria-current="page">{{ __('Create') }}</li>
                    </ol>
                </nav>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-body">
                            <h3 class="m-0 p-0">
                                {{ __('Create a New Instructor') }}</h3>
                        </div>
                    </div>
                </div>
            </div>

            <form action="{{ route('instructor.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="row">
                    <div class="col-md-12 mt-2">
                        <div class="card">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-4">
                                        <label class="form-label">{{ __('Name') }} <span class="text-danger fw-bold">*</span> </label>
                                        <input type="text" name="name" value="{{ old('name') }}" maxlength="50"
                                            id="instructorName" onchange="countNameChar()" class="form-control"
                                            placeholder="{{ __('Enter user name') }}">
                                        <div class="mt-2">
                                            <strong>{{ __('Characters') }}: <span id="charCountName">0</span>/50</strong>
                                        </div>
                                        @error('name')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">{{ __('Email') }} <span class="text-danger fw-bold">*</span> </label>
                                        <input type="email" name="email" value="{{ old('email') }}"
                                            class="form-control" placeholder="{{ __('Enter user email') }}">
                                        @error('email')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">{{ __('Phone') }} <span class="text-danger fw-bold">*</span> </label>
                                        <input type="text" name="phone" value="{{ old('phone') }}"
                                            class="form-control" placeholder="{{ __('Enter user phone') }}">
                                        @error('phone')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12 my-3">
                        <div class="card">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="row">
                                            <div class="col-md-12 mb-3">
                                                <label for="titleInput" class="form-label">{{ __('Instructor Title') }}</label>
                                                <input type="text" id="instructorTitle" name="title"
                                                    value="{{ old('title') }}" maxlength="60" onchange="updateCharCount()"
                                                    class="form-control" id="titleInput"
                                                    placeholder="{{ __('Enter instructor title') }}">
                                                <div class="mt-2">
                                                    <strong>{{ __('Characters') }}: <span id="charCount">0</span>/60</strong>
                                                </div>
                                                @error('title')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>
                                            <div class="col-md-12">
                                                <label for="aboutInput" class="form-label">{{ __('Instructor About') }}</label>
                                                <textarea name="about" rows="14" class="form-control" id="aboutInput" placeholder="{{ __('Enter about text') }}">{{ old('about') }}</textarea>
                                                @error('about')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="mb-3" style="width: 100%; height: 220px">
                                                    <img id="courseImagePreview" src="{{ asset('media/demoimage.png') }}"
                                                        class="w-100 h-100" style="border-radius:1rem; object-fit: contain">
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
                                                @error('profile_picture')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>

                                        </div>
                                    </div>
                                    <div class="col-md-12 mt-5">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <label class="form-label">{{ __('Password') }} <span
                                                        class="text-danger fw-bold">*</span>
                                                </label>
                                                <input type="password" name="password" class="form-control"
                                                    placeholder="{{ __('Enter user password') }}">
                                                @error('password')
                                                    <span class="text-danger">{{ $message }}</span>
                                                    <p class="text-info mt-2 fw-bold">
                                                        {{ __('Please follow these rules when creating a new password') }}:
                                                    </p>
                                                    <ul class="text-warning">
                                                        <li>{{ __('Your password must be at least 8 characters long') }}.</li>
                                                        <li>{{ __('At least one uppercase letter') }} (A-Z).</li>
                                                        <li>{{ __('At least one lowercase letter') }} (a-z).</li>
                                                        <li>{{ __('At least one numeric digit') }} (0-9).</li>
                                                        <li>{{ __('At least one special character') }} (e.g., !@#$%^&*).</li>
                                                    </ul>
                                                @enderror
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">{{ __('Confirm Password ') }}<span
                                                        class="text-danger fw-bold">*</span></label>
                                                <input type="password" name="password_confirmation" class="form-control"
                                                    placeholder="{{ __('Enter user password again') }}">
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
                                            <input id="featuredInput" name="is_featured"
                                                {{ old('is_featured') ? 'checked' : '' }} class="form-check-input"
                                                type="checkbox">
                                            <label for="featuredInput" class="form-check-label">
                                                {{ __('Feature on Homepage') }}
                                            </label>
                                        </div>
                                        <div class="form-check">
                                            <input id="activeInput" name="is_active"
                                                {{ old('is_active') ? 'checked' : '' }} class="form-check-input"
                                                type="checkbox">
                                            <label for="activeInput" class="form-check-label">
                                                {{ __('Verify Account by Default') }}
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

@push('scripts')
    {{-- instructor title char count --}}
    <script>
        // Wait for the DOM to fully load before adding the event listener
        document.addEventListener('DOMContentLoaded', function() {
            // Get references to the input field and character count display
            const titleInput = document.getElementById('instructorTitle');
            const charCountDisplay = document.getElementById('charCount');
            // Function to update the character count
            function updateCharCount() {
                charCountDisplay.textContent = titleInput.value.length;
            }
            // Attach the event listener to update count in real time
            titleInput.addEventListener('input', updateCharCount);
        });
    </script>


    {{-- instructor name char count --}}
    <script>
        // Wait for the DOM to fully load before adding the event listener
        document.addEventListener('DOMContentLoaded', function() {
            // Get references to the input field and character count display
            const titleInput = document.getElementById('instructorName');
            const charCountDisplay = document.getElementById('charCountName');
            // Function to update the character count
            function countNameChar() {
                charCountDisplay.textContent = titleInput.value.length;
            }
            // Attach the event listener to update count in real time
            titleInput.addEventListener('input', countNameChar);
        });
    </script>
@endpush
