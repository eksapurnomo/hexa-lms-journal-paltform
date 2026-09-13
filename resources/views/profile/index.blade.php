@extends('layouts.app')

@section('title', $app_setting['name'] . ' | ' . __('Profile'))

@section('content')

    <div class="app-main-outer">
        <div class="app-main-inner">
            {{-- top --}}
            <div class="row">
                <div class="col-lg-12 border-bottom border-bottom">
                    <h2 class="mb-3 text-uppercase fw-bold fs-4">{{ __('Profile') }}</h2>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-12 mt-4">
                    <div class="card">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-lg-12 d-flex justify-content-end">
                                    <a href="{{ route('user.edit', auth()->user()->id) }}">
                                        <img src="/assets/images/icon/color-edit.svg" alt="edit">
                                    </a>
                                </div>
                                <div class="col-md-4 col-lg-2 my-auto">
                                    <div class="profile-img">
                                        <img class="rounded-circle w-100 h-100 object-fit-contain"
                                            src="{{ auth()->user()->profilePicturePath }}" alt="profile ">
                                        <form action="{{ route('admin.profile.image.update', auth()->user()->id) }}"
                                            method="POST" enctype="multipart/form-data">
                                            @csrf @method('PUT')
                                            <div class="overlay-img">
                                                <label for="profile_picture">
                                                    <img src="/assets/images/icon/camera.svg" alt="edit">
                                                </label>
                                                <input type="file" name="profile_picture" id="profile_picture" hidden
                                                    accept="image/*" onchange="this.form.submit()">
                                            </div>
                                        </form>
                                    </div>
                                </div>
                                <div class="col-md-12 col-lg-10 my-auto mt-3 mt-lg-0">
                                    <div class="mb-3">
                                        <h3 class="profile-title">{{ auth()->user()->name }}</h3>
                                        <p class="profile-subtitle">{{ auth()->user()->email }}</p>
                                    </div>
                                    <div class="row rounded bg-primary-light py-4 px-3">
                                        <div class=" col-md-6 col-lg-3">
                                            <h4 class="profile-content-title">{{ __('Email') }} : </h4>
                                            <p class="profile-content-subtitle">{{ auth()->user()->email }}</p>
                                        </div>
                                        <div class=" col-md-6 col-lg-3">
                                            <h4 class="profile-content-title">{{ __('Phone Number') }} : </h4>
                                            <p class="profile-content-subtitle">{{ auth()->user()->phone }}</p>
                                        </div>
                                        <div class=" col-md-6 mt-3 mt-lg-0 col-lg-3">
                                            <h4 class="profile-content-title">{{ __('Gender') }} : </h4>
                                            <p class="profile-content-subtitle text-capitalize">
                                                {{ auth()->user()->gender ?? 'N/A' }}</p>
                                        </div>
                                        <div class=" col-md-6 mt-3 mt-lg-0 col-lg-3">
                                            <h4 class="profile-content-title">{{ __('Date of Birth') }} : </h4>
                                            <p class="profile-content-subtitle">
                                                {{ \Carbon\Carbon::parse(auth()->user()->birthday)->format('d M, Y') }}</p>
                                        </div>

                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row my-3">
                <div class="col-lg-8">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <h3 class="profile-info-title m-0 p-0">{{ __('About Me') }}</h3>
                                    <div class="edit d-flex align-items-start">
                                        <a href="{{ route('user.edit', auth()->user()->id) }}">
                                            <img src="/assets/images/icon/color-edit.svg" alt="edit">
                                        </a>
                                    </div>
                                </div>
                                <p class="profile-info-des">
                                    {{ auth()->user()->about ?? __('No information available right now') }}.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 mt-3 mt-lg-0">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between mb-4">
                                <h3 class="profile-info-title m-0 p-0">{{ __('Contact Info') }}</h3>
                                <div class="edit d-flex align-items-start">
                                    <a href="{{ route('user.edit', auth()->user()->id) }}">
                                        <img src="/assets/images/icon/color-edit.svg" alt="edit">
                                    </a>
                                </div>
                            </div>
                            <div class="mb-3">
                                <h4 class="profile-content-title">{{ __('Email') }} : </h4>
                                <p class="profile-content-subtitle">{{ auth()->user()->email }}</p>
                            </div>
                            <div class="mb-3">
                                <h4 class="profile-content-title">{{ __('Phone Number') }} : </h4>
                                <p class="profile-content-subtitle">{{ auth()->user()->phone }}</p>
                            </div>
                            <div>
                                <h4 class="profile-content-title">{{ __('Whatsapp Number') }} : </h4>
                                <p class="profile-content-subtitle">
                                    {{ auth()->user()->whatsapp ?? 'N/A' }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            {{-- bottom --}}
        </div>
    </div>

@endsection
