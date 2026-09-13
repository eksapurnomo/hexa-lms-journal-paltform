@extends('layouts.app')

@section('title', $app_setting['name'] . ' | ' . __('Certificate Configaration'))

@push('styles')
    <style>
        .additionThumbnail.active {
            border-color: rgb(25, 216, 25);

        }
    </style>
@endpush

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
                            <h3 class="m-0 p-0 fw-bold">
                                {{ __('Certificate Configaration') }}</h3>
                        </div>
                    </div>
                </div>
            </div>


            <form action="{{ route('certificate.update') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="row">
                    <div class="col-md-12 my-3">
                        <div class="card">
                            <div class="card-body">
                                <div class="row">
                                    {{-- <div class="col-md-12 mb-4">
                                        <h4 class="form-label">{{ __('Choose Frame') }}</h4>
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div id="frameOne" class="{{ $certificate?->frame == 1 ? 'active' : '' }}"
                                                    onclick="selectFrame('themeone')">
                                                    <div id="border-frame">
                                                        <img src="{{ asset('enrollment/themeone.png') }}" alt="themeone">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div id="frameTwo" class="{{ $certificate?->frame == 2 ? 'active' : '' }}"
                                                    onclick="selectFrame('themetwo')">
                                                    <div id="border-frame">
                                                        <img src="{{ asset('enrollment/themetwo.png') }}" alt="themetwo">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <input id="frameInput" name="frame" type="text" hidden value="">
                                    </div> --}}
                                    <div class="col-md-12 mb-4">
                                        <h4 class="form-label">Choose Frame</h4>
                                        <div class="row">
                                            @foreach ($frames as $frame)
                                                <div class="col-md-3 col-lg-2">
                                                    <label id="attribute_38_thumbnail{{ $frame->id }}"
                                                        for="attribute_38_thumbnail"
                                                        class="additionThumbnail {{ $certificate?->frame_id == $frame->id ? 'active' : '' }}"
                                                        onclick="selectFrame({{ $frame->id }})"
                                                        style="width: 100%; height: 150px; object-fit: fill">
                                                        <img src="{{ Storage::url($frame->src) }}" alt=""
                                                            width="100%" height="100%">
                                                        <a href="{{ route('certificate.delete', $frame->id) }}"
                                                            id="removeAttribute38Thumbnail0" type="button"
                                                            class="delete btn btn-sm btn-outline-danger circleIcon">
                                                            <img src="{{ asset('assets/images/icon/trash.svg') }}"
                                                                loading="lazy" alt="trash">
                                                        </a>
                                                    </label>
                                                </div>
                                            @endforeach
                                            <div class="col-md-2">
                                                <label for="uploadFrame" class="additionThumbnail"
                                                    style="width: 100%; height: 150px; object-fit: fill">
                                                    <img src="{{ asset('enrollment/upload.png') }}"
                                                        id="attribute38preview0" alt="" width="100%"
                                                        height="100%">
                                                    <button onclick="" id="removeAttribute38Thumbnail0" type="button"
                                                        class="delete btn btn-sm btn-outline-danger circleIcon"
                                                        style="display: none">
                                                        <img src="{{ asset('assets/images/icon/trash.svg') }}"
                                                            loading="lazy" alt="trash">
                                                    </button>
                                                </label>
                                                <input id="uploadFrame" accept="image/*" type="file" name="frame_id"
                                                    class="d-none"
                                                    onchange="document.getElementById('attribute38preview0').src = window.URL.createObjectURL(this.files[0])">
                                            </div>
                                        </div>
                                        <input id="frameInput" name="selected_frame" type="text" hidden value="">
                                    </div>

                                    <div class="col-md-12">
                                        <h4 class="form-label mb-4">{{ __('Choose Logo & Signature') }}</h4>
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="mb-3 d-flex justify-content-center">
                                                    <div style="width: 100%; height:150px;">
                                                        <img id="siteLogoPreview" src="{{ $certificate?->siteLogoPath }}"
                                                            class="w-100 h-100" style="object-fit: fill">
                                                    </div>
                                                </div>
                                                <h4 class="form-label">{{ __('Site Logo') }} (JPG, JPEG, PNG)*</h4>
                                                <label for="formFileSiteLogoImage" class="w-100 border rounded-3">
                                                    <div class="d-flex justify-content-center align-items-center gap-2 p-3"
                                                        style="width: 160px; background-color: #EDEEF1">
                                                        <span>{{ __('Choose a file') }}</span>
                                                        <img src="/assets/images/media/file-plus.svg">
                                                    </div>
                                                </label>
                                                <input name="site_logo_id" class="form-control form-control-lg"
                                                    id="formFileSiteLogoImage" type="file" hidden
                                                    onchange="document.getElementById('siteLogoPreview').src = window.URL.createObjectURL(this.files[0])" />
                                                @error('site_logo_id')
                                                    <span class="text-danger mt-2">{{ $message }}</span>
                                                @enderror
                                            </div>
                                            {{-- <div class="col-md-4">
                                                <div class="mb-3 d-flex justify-content-center">
                                                    <div style="width: 100%; height:150px;">
                                                        <img id="subSiteLogoPreview"
                                                            src="{{ $certificate?->subSiteLogoPath }}" class="w-100 h-100"
                                                            style="object-fit: fill">
                                                    </div>
                                                </div>
                                                <h4 class="form-label">{{ __('Sub Site Logo') }} (JPG, JPEG, PNG)*</h4>
                                                <label for="formFileSubSiteLogoImage" class="w-100 border rounded-3">
                                                    <div class="d-flex justify-content-center align-items-center gap-2 p-3"
                                                        style="width: 160px; background-color: #EDEEF1">
                                                        <span>{{ __('Choose a file') }}</span>
                                                        <img src="/assets/images/media/file-plus.svg">
                                                    </div>
                                                </label>
                                                <input name="subsite_logo_id" class="form-control form-control-lg"
                                                    id="formFileSubSiteLogoImage" type="file" hidden
                                                    onchange="document.getElementById('subSiteLogoPreview').src = window.URL.createObjectURL(this.files[0])" />
                                                @error('subsite_logo_id')
                                                    <span class="text-danger mt-2">{{ $message }}</span>
                                                @enderror
                                            </div> --}}
                                            <div class="col-md-4">
                                                <div class="mb-3 d-flex justify-content-center">
                                                    <div style="width: 100%; height:150px;">
                                                        <img id="authSignaturePreview"
                                                            src="{{ $certificate?->authSignaturePath }}"
                                                            class="w-100 h-100" style="object-fit: fill">
                                                    </div>
                                                </div>
                                                <h4 class="form-label">{{ __('Author Signature') }} (JPG, JPEG, PNG)*</h4>
                                                <label for="formFileSignatureImage" class="w-100 border rounded-3">
                                                    <div class="d-flex justify-content-center align-items-center gap-2 p-3"
                                                        style="width: 160px; background-color: #EDEEF1">
                                                        <span>{{ __('Choose a file') }}</span>
                                                        <img src="/assets/images/media/file-plus.svg">
                                                    </div>
                                                </label>
                                                <input name="author_signature_id" class="form-control form-control-lg"
                                                    id="formFileSignatureImage" type="file" hidden
                                                    onchange="document.getElementById('authSignaturePreview').src = window.URL.createObjectURL(this.files[0])" />
                                                @error('author_signature_id')
                                                    <span class="text-danger mt-2">{{ $message }}</span>
                                                @enderror
                                            </div>

                                        </div>
                                    </div>
                                    <div class="col-md-12 mt-3">
                                        <div class="row">
                                            <div class="col-md-12 mb-3">
                                                <h4 class="form-label mb-4">{{ __('Certificate Information') }} :</h4>
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label class="form-label">{{ __('Certificate Purpose Title') }} <span
                                                        class="text-danger">*</span></label>
                                                <input type="text" name="certificate_title" id="certificate_title"
                                                    class="form-control" placeholder="Enter user name"
                                                    value="{{ $certificate?->certificate_title }}" maxlength="15"
                                                    onchange="updateTitleCount()">
                                                <strong>{{ __('Characters') }}: <span
                                                        id="updateTitleCount">0</span>/15</strong>
                                                <div class="mt-2">
                                                    @error('certificate_title')
                                                        <p class="text-danger my-2">{{ $message }}</p>
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label class="form-label">{{ __('Certificate Short Text') }} <span
                                                        class="text-danger">*</span></label>
                                                <textarea name="certificate_short_text" id="certificate_short_text" rows="3" class="form-control"
                                                    maxlength="50" onchange="updateShortTextCount()">{{ $certificate?->certificate_short_text }}</textarea>
                                                <strong>{{ __('Characters') }}: <span
                                                        id="updateShortTextCount">0</span>/50</strong>
                                                @error('certificate_short_text')
                                                    <p class="text-danger my-2">{{ $message }}</p>
                                                @enderror
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label class="form-label">{{ __('Certificate Full Text') }} </label>
                                                <textarea name="certificate_text" id="certificate_text" rows="3" class="form-control" maxlength="250"
                                                    onchange="updateDescriptionCount()">{{ $certificate?->certificate_text }}</textarea>
                                                <strong>{{ __('Characters') }} <span
                                                        id="updateDescriptionCount">0</span>/250</strong>
                                                <p class="m-0 text-muted mt-2"><span class="text-danger">*</span>
                                                    {{ __('use') }}
                                                    {course_name} {{ __('to display course name') }}</p>
                                                @error('certificate_text')
                                                    <p class="text-danger my-2">{{ $message }}</p>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-12 mb-3">
                                        <h4 class="form-label mb-4">{{ __('Athor Information') }} :</h4>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">{{ __('Author Name') }} <span
                                                class="text-danger">*</span></label>
                                        <input type="text" name="author_name" class="form-control"
                                            placeholder="Enter user name" value="{{ $certificate?->author_name }}">
                                        @error('author_name')
                                            <p class="text-danger my-2">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">{{ __('Author Designation') }} <span
                                                class="text-danger">*</span></label>
                                        <input type="text" name="author_designation" class="form-control"
                                            placeholder="{{ __('Enter Designation') }}"
                                            value="{{ $certificate?->author_designation }}">
                                        @error('author_designation')
                                            <p class="text-danger my-2">{{ $message }}</p>
                                        @enderror
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
                                <div class="row">
                                    <div class="col-md-12 d-flex justify-content-end">
                                        <button type="submit"
                                            class="btn btn-primary btn-lg px-5 py-2">{{ __('Update') }}</button>
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
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const titleInput = document.getElementById('certificate_title');
            const charCountDisplay = document.getElementById('updateTitleCount');
            const shortTextInput = document.getElementById('certificate_short_text');
            const shortTextDisplay = document.getElementById('updateShortTextCount');
            const textInput = document.getElementById('certificate_text');
            const textDisplay = document.getElementById('updateDescriptionCount');


            charCountDisplay.textContent = titleInput.value.length;
            shortTextDisplay.textContent = shortTextInput.value.length;
            textDisplay.textContent = textInput.value.length;


            function updateTitleCount() {
                charCountDisplay.textContent = titleInput.value.length;
            }

            function updateShortTextCount() {
                shortTextDisplay.textContent = shortTextInput.value.length;
            }

            function updateDescriptionCount() {
                textDisplay.textContent = textInput.value.length;
            }

            titleInput.addEventListener('input', updateTitleCount);
            shortTextInput.addEventListener('input', updateShortTextCount);
            textInput.addEventListener('input', updateDescriptionCount);
        });
    </script>

    {{-- <script>
        function selectFrame(theme) {
            // Ensure both inputs exist
            let frameInput = document.getElementById('frameInput');
            const frameOne = document.getElementById('frameOne');
            const frameTwo = document.getElementById('frameTwo');

            frameOne.classList.remove('active');
            frameTwo.classList.remove('active');


            if (frameInput && theme == 'themeone') {
                frameInput.value = '1';
                frameOne.classList.add('active');
            } else if (frameInput && theme === 'themetwo') {
                frameInput.value = '2';
                frameTwo.classList.add('active');
            }
        }
    </script> --}}

    <script>
        let lastSelectedFrame = null;

        function selectFrame(id) {
            let frame = document.getElementById('attribute_38_thumbnail' + id);
            let frameInput = document.getElementById('frameInput');
            frameInput.value = id;
            // if (frame) {
            //     if (lastSelectedFrame) {
            //         lastSelectedFrame.classList.remove('active');
            //     }
            //     frame.classList.toggle('active');
            //     lastSelectedFrame = frame;
            // }
            let allFrames = document.querySelectorAll('[id^="attribute_38_thumbnail"]');
            allFrames.forEach(f => f.classList.remove('active'));

            // Add 'active' class to selected frame
            if (frame) {
                frame.classList.add('active');
            }
        }


        document.addEventListener("DOMContentLoaded", function() {
            if (localStorage.getItem("frameDeleted") === "true") {
                localStorage.removeItem("frameDeleted");

                // Show success message after reload
                const Toast = Swal.mixin({
                    toast: true,
                    position: "top-end",
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true,
                    didOpen: (toast) => {
                        toast.onmouseenter = Swal.stopTimer;
                        toast.onmouseleave = Swal.resumeTimer;
                    }
                });
                Toast.fire({
                    icon: "success",
                    title: "Frame deleted successfully"
                });
            }
        });
    </script>
@endpush
