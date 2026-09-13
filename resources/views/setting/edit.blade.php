@extends('layouts.app')

@section('title', $app_setting['name'] . ' | ' . __('Settings'))

@section('content')
    <!-- ****Body-Section***** -->
    <div class="app-main-outer">
        <div class="app-main-inner">
            <div class="page-title-actions px-3 d-flex">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }}</a></li>
                        <li class="breadcrumb-item active" aria-current="page">{{ __('Settings') }}</li>
                    </ol>
                </nav>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <h3 class="m-0 p-0">
                                {{ __('Settings') }}</h3>
                        </div>
                    </div>
                </div>
            </div>


            <div class="row">
                <div class="col-12">
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="m-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </div>
            </div>

            <form action="{{ route('setting.update') }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="row my-3">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-4 border-end mt-auto">
                                        <div class="mb-3">
                                            <img id="logoImagePreview" src="{{ $setting->logoPath }}" class="w-100"
                                                style="max-height: 180px; border-radius:1rem; object-fit: contain">
                                        </div>
                                        <div>
                                            <h4 class="form-label">{{ __('System Logo') }} (JPG, JPEG, PNG)*</h4>
                                            <label for="formLogoImage" class="w-100 border rounded-3">
                                                <div class="d-flex justify-content-center align-items-center gap-2 p-3"
                                                    style="width: 160px; background-color: #EDEEF1">
                                                    <span>{{ __('Choose a file') }}</span>
                                                    <img src="/assets/images/media/file-plus.svg">
                                                </div>
                                            </label>
                                            <input name="logo" class="form-control form-control-lg" id="formLogoImage"
                                                type="file" hidden
                                                onchange="document.getElementById('logoImagePreview').src = window.URL.createObjectURL(this.files[0])" />
                                        </div>
                                    </div>
                                    <div class="col-md-4 border-end mt-auto">
                                        <div class="mb-3">
                                            <img id="footerImagePreview" src="{{ $setting->footerPath }}" class="w-100"
                                                style="max-height: 160px; border-radius:1rem; object-fit: contain">
                                        </div>
                                        <div>
                                            <h4 class="form-label">{{ __('System Footer Logo') }} (JPG, JPEG, PNG)*</h4>
                                            <label for="formFooterImage" class="w-100 border rounded-3">
                                                <div class="d-flex justify-content-center align-items-center gap-2 p-3"
                                                    style="width: 160px; background-color: #EDEEF1">
                                                    <span>{{ __('Choose a file') }}</span>
                                                    <img src="/assets/images/media/file-plus.svg">
                                                </div>
                                            </label>
                                            <input name="footerlogo" class="form-control form-control-lg"
                                                id="formFooterImage" type="file" hidden
                                                onchange="document.getElementById('footerImagePreview').src = window.URL.createObjectURL(this.files[0])" />
                                        </div>
                                    </div>
                                    <div class="col-md-4 border-end mt-auto">
                                        <div class="mb-3">
                                            <img id="faviconImagePreview" src="{{ $setting->faviconPath }}" class="w-100"
                                                style="max-height: 110px; border-radius:1rem; object-fit: contain">
                                        </div>
                                        <div>
                                            <h4 class="form-label">{{ __('Website Favicon') }} (JPG, JPEG, PNG)*</h4>
                                            <label for="formFaviconImage" class="w-100 border rounded-3">
                                                <div class="d-flex justify-content-center align-items-center gap-2 p-3"
                                                    style="width: 160px; background-color: #EDEEF1">
                                                    <span>{{ __('Choose a file') }}</span>
                                                    <img src="/assets/images/media/file-plus.svg">
                                                </div>
                                            </label>
                                            <input name="favicon" class="form-control form-control-lg" id="formFaviconImage"
                                                type="file" hidden
                                                onchange="document.getElementById('faviconImagePreview').src = window.URL.createObjectURL(this.files[0])" />
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>


                <div class="row">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-body">
                                <div class="row">

                                    <div class="col-md-12">
                                        <div class="row">
                                            <div class="col-md-12 mb-3">
                                                <p class="fw-bold border-bottom border-2 pb-3 ">
                                                    {{ __('System Information') }}</p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-4 mb-3">
                                        <label for="courseTitle" class="form-label">{{ __('System Title') }} <span
                                                class="text-danger fw-bold">*</span></label>
                                        <input type="text" class="form-control" id="courseTitle" name="app_name"
                                            value="{{ config('app.name') }}" />
                                        @error('app_name')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="courseTitle" class="form-label">{{ __('Currency') }} <span
                                                class="text-danger fw-bold">*</span></label>
                                        <input type="text" class="form-control" id="courseTitle" name="app_currency"
                                            value="{{ config('app.currency') }}" />
                                        @error('app_currency')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="timezoneInput" class="form-label">{{ __('Timezone') }}</label>
                                        <select id="instructorInput" class="form-select form-control"
                                            style="width: 100%;" name="app_timezone" aria-hidden="true">
                                            @foreach ($timezones as $timezone)
                                                <option value="{{ $timezone['zone'] }}"
                                                    {{ $timezone['zone'] === config('app.timezone') ? 'selected="selected"' : '' }}>
                                                    {{ $timezone['diff_from_GMT'] }} - {{ $timezone['zone'] }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('Timezone')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div class="col-md-4 mb-3">
                                        <label for="courseTitle" class="form-label">{{ __('Currency Symbol') }}<span
                                                class="text-danger fw-bold">*</span></label>
                                        <input type="text" class="form-control" id="courseTitle"
                                            name="app_currency_symbol" value="{{ config('app.currency_symbol') }}" />
                                        @error('app_currency_symbol')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label" for="currencypositionInput">
                                            {{ __('Currency Position') }}
                                        </label>
                                        <select id="currencypositionInput" class="form-select form-control"
                                            style="width: 100%;" name="currency_position" aria-hidden="true">
                                            <option value="Left"
                                                {{ $setting->currency_position === 'Left' ? 'selected' : '' }}>
                                                {{ __('Left') }}
                                            </option>
                                            <option value="Right"
                                                {{ $setting->currency_position === 'Right' ? 'selected' : '' }}>
                                                {{ __('Right') }}
                                            </option>
                                        </select>
                                        @error('currency_position')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div class="col-md-4 mb-3">
                                        <label for="courseTitle" class="form-label">{{ __('Minimum Amount to Buy Courses') }} <span style="font-size: 0.75rem; font-family: monospace;">({{ __('Depends on Region') }})</span><span
                                                class="text-danger fw-bold">*</span></label>
                                        <input type="text" class="form-control" id="courseTitle"
                                            name="app_minimum_amount" value="{{ config('app.minimum_amount') }}" />
                                        @error('app_minimum_amount')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div class="col-md-12">
                                        <div class="row">
                                            <div class="col-md-12 my-3">
                                                <p class="fw-bold border-bottom border-2 pb-3 ">
                                                    {{ __('Footer Information') }}</p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-4 mb-3">
                                        <label for="courseTitle" class="form-label">{{ __('Footer text') }}
                                            <span class="text-danger fw-bold">*</span>
                                        </label>
                                        <input type="text" class="form-control" id="courseTitle" name="footer_text"
                                            value="{{ $setting->footer_text }}" />
                                        @error('footer_text')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div class="col-md-4">
                                        <div class="row">
                                            <div class="col-md-12 mb-3">
                                                <label for="footer_contact_number" class="form-label">
                                                    {{ __('Footer Contact Number') }}(+tel)
                                                    <span class="text-danger fw-bold">*</span>
                                                </label>
                                                <input type="tel" class="form-control" placeholder="+8801XXXXXXXXX"
                                                    id="footer_contact_number" name="{{ __('footer_contact_number') }}"
                                                    value="{{ $setting->footer_contact_number }}">
                                                @error('footer_contact_number')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="row">
                                            <div class="col-md-12 mb-3">
                                                <label for="footer_support_mail" class="form-label">
                                                    {{ __('Footer Support Mail') }}
                                                    <span class="text-danger fw-bold">*</span>
                                                </label>
                                                <input type="email" class="form-control" id="footer_support_mail"
                                                    placeholder="support@example.com" name="footer_support_mail"
                                                    value="{{ $setting->footer_support_mail }}">
                                                @error('footer_support_mail')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-12">
                                        <div class="row">
                                            <div class="col-md-12 mb-3">
                                                <label for="footer_description"
                                                    class="form-label">{{ __('Footer Description') }}<span
                                                        class="text-danger fw-bold">*</span></label>
                                                <textarea class="form-control" name="footer_description" id="footer_description" rows="3"
                                                    placeholder="{{ __('Footer Description') }}">{{ $setting->footer_description ?? '' }}</textarea>
                                                @error('app_currency_symbol')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-12">
                                        <div class="row">
                                            <div class="col-md-12 my-3">
                                                <p class="fw-bold border-bottom border-2 pb-3 ">
                                                    {{ __('Social Media Link') }}</p>
                                            </div>
                                        </div>
                                    </div>

                                    @foreach ($socialMedias as $media)
                                        <div class="col-md-4 mb-3">
                                            <label for="facebook_link" class="form-label"> <i
                                                    class="{{ $media->icon }} fs-6"></i>
                                                {{ $media->title }} {{ __('Link') }}</label>
                                            <input type="text" class="form-control" id="facebook_link"
                                                name="social_links[{{ $media->id }}]"
                                                placeholder="{{ $media->url ?? $media->title }} Link"
                                                value="" />
                                            @error('social_links.' . $media->id)
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    @endforeach

                                    <div class="col-md-12">
                                        <div class="row">
                                            <div class="col-md-12 my-3">
                                                <p class="fw-bold border-bottom border-2 pb-3 ">
                                                    {{ __('Downloadable Link') }}</p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-12">
                                        <div class="row">

                                            <div class="col-md-4 mt-auto">
                                                <div class="row">
                                                    <div class="col-md-12">
                                                        <label for="play_store" class="form-label"> <i
                                                                class="bi bi-google-play fs-6"></i>
                                                            {{ __('Play Store') }}<span
                                                                class="text-danger fw-bold">*</span></label>
                                                        <input type="text" class="form-control" id="play_store"
                                                            placeholder="{{ __('Play Store Link') }}"
                                                            name="play_store_url" value="{{ $setting->play_store_url }}">
                                                        @error('play_store_url')
                                                            <span class="text-danger">{{ $message }}</span>
                                                        @enderror
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-4 mt-auto">
                                                <div class="row">
                                                    <div class="col-md-12">
                                                        <label for="app_store" class="form-label"><i
                                                                class="bi bi-apple fs-6"></i> {{ __('App Store') }}<span
                                                                class="text-danger fw-bold">*</span></label>
                                                        <input type="text" class="form-control" id="app_store"
                                                            placeholder="{{ __('App Store Link') }}" name="app_store_url"
                                                            value="{{ $setting->app_store_url }}">
                                                        @error('app_store_url')
                                                            <span class="text-danger">{{ $message }}</span>
                                                        @enderror
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-4 my-auto">
                                                <div class="mb-3 d-flex justify-content-center">
                                                    <img id="scanerImagePreview" src="{{ $setting->scanerPath }}"
                                                        style="width:100px; height:100px; border-radius:1rem; object-fit: contain">
                                                </div>
                                                <div>
                                                    <h4 class="form-label">{{ __('Website QR Scaner') }}(JPG, JPEG, PNG)*
                                                    </h4>
                                                    <label for="formScanerImage" class="w-100 border rounded-3">
                                                        <div class="d-flex justify-content-center align-items-center gap-2 p-3"
                                                            style="width: 160px; background-color: #EDEEF1">
                                                            <span>{{ __('Choose a file') }}</span>
                                                            <img src="/assets/images/media/file-plus.svg">
                                                        </div>
                                                    </label>
                                                    <input name="scaner" class="form-control form-control-lg"
                                                        id="formScanerImage" type="file" hidden
                                                        onchange="document.getElementById('scanerImagePreview').src = window.URL.createObjectURL(this.files[0])" />
                                                </div>
                                            </div>
                                        </div>
                                    </div>


                                    <div class="col-md-12">
                                        <div class="row">
                                            <div class="col-md-12 my-3">
                                                <p class="fw-bold border-bottom border-2 pb-3 ">
                                                    {{ __('Mail Credentials') }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-12">
                                        <div class="row">
                                            <div class="col-md-4">
                                                <label for="mail_host" class="form-label">SMTP Host</label>
                                                <input type="text" class="form-control" id="mail_host"
                                                    name="mail_host"
                                                    value="{{ config('mail.mailers.smtp.host') ?? '' }}" />
                                                @error('mail_host')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>
                                            <div class="col-md-4">
                                                <label for="mail_port" class="form-label">SMTP
                                                    {{ __('Email Port No') }}.</label>
                                                <input type="text" class="form-control" id="mail_port"
                                                    name="mail_port"
                                                    value="{{ config('mail.mailers.smtp.port') ?? '' }}" />
                                                @error('mail_port')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>
                                            <div class="col-md-4">
                                                <label for="mail_encryption" class="form-label">SMTP Encryption.</label>
                                                <input type="text" class="form-control" id="mail_encryption"
                                                    name="mail_encryption"
                                                    value="{{ config('mail.mailers.smtp.encryption') ?? '' }}" />
                                                @error('mail_encryption')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>
                                            <div class="col-md-4 mt-3">
                                                <label for="mail_user" class="form-label">SMTP
                                                    {{ __('Username') }}</label>
                                                <input type="text" class="form-control" id="mail_user"
                                                    name="mail_user"
                                                    value="{{ config('mail.mailers.smtp.username') ?? '' }}" />
                                                @error('mail_user')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>
                                            <div class="col-md-4 mt-3">
                                                <label for="mail_password" class="form-label">SMTP
                                                    {{ __('Password') }}</label>
                                                <input type="text" class="form-control" id="mail_password"
                                                    name="mail_password"
                                                    value="{{ config('mail.mailers.smtp.password') ?? '' }}" />
                                                @error('mail_password')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>
                                            <div class="col-md-4 mt-3">
                                                <label for="mail_address" class="form-label">SMTP
                                                    {{ __('Address') }}</label>
                                                <input type="text" class="form-control" id="mail_address"
                                                    name="mail_address"
                                                    value="{{ config('mail.mailers.smtp.address') }}" />
                                                @error('mail_address')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-12">
                                        <div class="row">
                                            <div class="col-md-12 my-3">
                                                <p class="fw-bold border-bottom border-2 pb-3 ">API
                                                    {{ __('Credentials') }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-12">
                                        <div class="row">
                                            <div class="col-md-4">
                                                <label for="jwt_secret" class="form-label">JWT
                                                    {{ __('Secret Key') }}</label>
                                                <input type="text" class="form-control" id="jwt_secret"
                                                    name="jwt_secret" value="{{ config('jwt.secret') ?? '' }}" />
                                                @error('jwt_secret')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>



                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12 mt-2 mb-3">
                        <div class="card">
                            <div class="card-body">
                                <button type="submit" class="btn btn-primary px-5 py-2">{{ __('Update') }}</button>
                            </div>
                        </div>
                    </div>
                </div>

            </form>


        </div>


        <!-- ****End-Body-Section**** -->
    </div>
    </div>
@endsection
