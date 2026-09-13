@extends('layouts.app')

@section('title', $app_setting['name'] . ' | Chapter Create')

@section('content')
    <style>
        .hidden {
            display: none;
        }

        #progressLoader {
            position: fixed;
            z-index: 99999;
            width: 100%;
            height: 100vh;
            background-color: rgba(0, 0, 0, 0.7);
            display: none;
            justify-content: center;
            align-items: center;
            flex-direction: column;
            color: #252525;
            text-align: center;
        }

        .file-animation,
        .server-animation,
        .arrow-animation {
            width: 100px;
            height: 100px;
            animation: moveAnimation 2s infinite ease-in-out;
        }

        .file-animation {
            animation-delay: 0s;
        }

        .server-animation {
            animation-delay: 0.5s;
        }

        .arrow-animation {
            animation-delay: 1s;
            transform-origin: center;
        }

        .loaderWrapper {
            background: rgba(255, 255, 255, 0.856);
            padding: 20px;
            border-radius: 12px;
            backdrop-filter: blur(5px);
        }


        @keyframes moveAnimation {
            0% {
                transform: translateY(0);
            }

            50% {
                transform: translateY(-20px);
            }

            100% {
                transform: translateY(0);
            }
        }

        .arrow-animation {
            animation: arrowMove 2s infinite ease-in-out;
        }

        @keyframes arrowMove {
            0% {
                transform: translateX(0);
                opacity: 0;
            }

            50% {
                transform: translateX(30px);
                opacity: 1;
            }

            100% {
                transform: translateX(0);
                opacity: 0;
            }
        }
    </style>



    <!-- ****Body-Section***** -->
    <div class="app-main-outer">
        <div id="progressLoader">
            <div class="d-flex flex-column align-items-center loaderWrapper" style="transform: translateX(-50%)">
                <h2>Uploading Contents ... <span id="totalupload">0</span>/<span id="totalitem"></span> </h2>
                <p class="mb-5">Please do not close this window.</p>
                <div class="d-flex gap-5">
                    <!-- File icon -->
                    <svg class="file-animation" width="60" height="60" viewBox="0 0 60 60" fill="none"
                        xmlns="http://www.w3.org/2000/svg">
                        <path
                            d="M56.0974 28.5075C55.0799 26.77 53.2651 25.7325 51.2476 25.73H49.375V25C49.375 18.955 46.045 15.625 40 15.625H25.7776L21.3275 11.175C20.975 10.8225 20.4999 10.625 20.0024 10.625H12.5024C6.45744 10.625 3.12744 13.955 3.12744 20V46.24C3.12744 46.25 3.13232 46.2575 3.13232 46.2675C3.13232 47.2425 3.38509 48.2175 3.90259 49.1C4.92259 50.8375 6.73738 51.875 8.75488 51.875H40.7074C44.1074 51.875 47.2451 50.035 48.9001 47.075L56.1572 34.0725C57.1347 32.32 57.1149 30.24 56.0974 28.5075ZM12.5 14.375H19.2224L23.6725 18.825C24.025 19.1775 24.5001 19.375 24.9976 19.375H39.9976C43.9401 19.375 45.6226 21.0575 45.6226 25V25.73L19.2999 25.715C19.2974 25.715 19.2951 25.715 19.2926 25.715C15.8901 25.715 12.7499 27.555 11.0974 30.52L6.875 38.095V20C6.875 16.0575 8.5575 14.375 12.5 14.375ZM52.8827 32.245L45.625 45.2475C44.635 47.0225 42.7499 48.1275 40.7074 48.1275H8.75488C7.78238 48.1275 7.29995 47.4825 7.13745 47.205C6.97745 46.93 6.65231 46.2025 7.11731 45.3675L14.375 32.35C15.365 30.5725 17.25 29.47 19.295 29.47C19.2975 29.47 19.2974 29.47 19.2999 29.47L51.2476 29.4875C52.2201 29.4875 52.7025 30.135 52.8625 30.4125C53.025 30.68 53.3502 31.41 52.8827 32.245Z"
                            fill="#000000" />
                    </svg>

                    <!-- Arrow icon -->
                    <svg class="arrow-animation" width="60" height="60" viewBox="0 0 60 60" fill="none"
                        xmlns="http://www.w3.org/2000/svg">
                        <path
                            d="M43.825 31.325L33.825 41.325C33.46 41.69 32.9799 41.8749 32.4999 41.8749C32.0199 41.8749 31.5399 41.6925 31.1749 41.325C30.4424 40.5925 30.4424 39.4049 31.1749 38.6724L39.8498 29.9975L31.1749 21.3226C30.4424 20.5901 30.4424 19.4025 31.1749 18.67C31.9074 17.9375 33.095 17.9375 33.8275 18.67L43.8275 28.67C44.5575 29.4075 44.5575 30.5925 43.825 31.325ZM18.825 18.6749C18.0925 17.9424 16.9049 17.9424 16.1724 18.6749C15.4399 19.4074 15.4399 20.595 16.1724 21.3275L24.8473 30.0024L16.1724 38.6773C15.4399 39.4098 15.4399 40.5974 16.1724 41.3299C16.5374 41.6949 17.0175 41.8798 17.4975 41.8798C17.9775 41.8798 18.4576 41.6974 18.8226 41.3299L28.8226 31.3299C29.5551 30.5974 29.5551 29.4098 28.8226 28.6773L18.825 18.6749Z"
                            fill="#000000" />
                    </svg>


                    <!-- Server/Globe icon -->
                    <svg class="server-animation" width="60" height="60" viewBox="0 0 60 60" fill="none"
                        xmlns="http://www.w3.org/2000/svg">
                        <path
                            d="M54.375 22.5V15C54.375 8.955 51.045 5.625 45 5.625H15C8.955 5.625 5.625 8.955 5.625 15V22.5C5.625 25.915 6.72279 28.43 8.73779 30C6.72279 31.57 5.625 34.085 5.625 37.5V45C5.625 51.045 8.955 54.375 15 54.375H45C51.045 54.375 54.375 51.045 54.375 45V37.5C54.375 34.085 53.2772 31.57 51.2622 30C53.2772 28.43 54.375 25.915 54.375 22.5ZM50.625 37.5V45C50.625 48.9425 48.9425 50.625 45 50.625H15C11.0575 50.625 9.375 48.9425 9.375 45V37.5C9.375 33.5575 11.0575 31.875 15 31.875H45C48.9425 31.875 50.625 33.5575 50.625 37.5ZM15 28.125C11.0575 28.125 9.375 26.4425 9.375 22.5V15C9.375 11.0575 11.0575 9.375 15 9.375H45C48.9425 9.375 50.625 11.0575 50.625 15V22.5C50.625 26.4425 48.9425 28.125 45 28.125H15ZM38.75 18.7799C38.75 20.1599 37.63 21.2799 36.25 21.2799C34.87 21.2799 33.75 20.1599 33.75 18.7799C33.75 17.3999 34.87 16.2799 36.25 16.2799C37.63 16.2799 38.75 17.3999 38.75 18.7799ZM46.25 18.7799C46.25 20.1599 45.13 21.2799 43.75 21.2799C42.37 21.2799 41.25 20.1599 41.25 18.7799C41.25 17.3999 42.37 16.2799 43.75 16.2799C45.13 16.2799 46.25 17.3999 46.25 18.7799ZM33.75 41.25C33.75 39.87 34.87 38.75 36.25 38.75C37.63 38.75 38.75 39.87 38.75 41.25C38.75 42.63 37.63 43.75 36.25 43.75C34.87 43.75 33.75 42.63 33.75 41.25ZM41.25 41.25C41.25 39.87 42.37 38.75 43.75 38.75C45.13 38.75 46.25 39.87 46.25 41.25C46.25 42.63 45.13 43.75 43.75 43.75C42.37 43.75 41.25 42.63 41.25 41.25Z"
                            fill="#000000" />
                    </svg>

                </div>
                <!-- Progress Bar -->
                <div class="progress w-100 mt-3" role="progressbar" aria-label="Animated striped example" aria-valuenow="0"
                    aria-valuemin="0" aria-valuemax="100">
                    <div class="progress-bar text-bg-success progress-bar-striped progress-bar-animated" style="width: 0%">
                        0%</div>
                </div>

            </div>
        </div>
        <div class="app-main-inner">
            <div class="page-title-actions px-3 d-flex">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('course.index') }}">Course</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('chapter.index', $selectedCourse->id) }}">Chapter</a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">Create</li>
                    </ol>
                </nav>
            </div>


            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-body">
                            <h3 class="m-0 p-0">
                                {{ __('Create a New Chapter') }}</h3>
                        </div>
                    </div>
                </div>
            </div>

            <form id="chapterForm" enctype="multipart/form-data">
                @csrf

                @if ($errors->any())
                    <div class="row">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-body">

                                    <div class="alert alert-danger">
                                        <ul class="m-0">
                                            @foreach ($errors->all() as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                <div class="row">
                    <div class="col-md-12 my-3">
                        <div class="card">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-12 col-xxl-5 mb-3 mb-xxl-0">
                                        <label class="form-label" for="categoryInput">Course Title <span
                                                class="text-danger fw-bold">*</span></label>
                                        <select id="categoryInput" class="form-select form-control" name="course_id"
                                            aria-hidden="true">
                                            @foreach ($courses as $course)
                                                <option value="{{ $course->id }}"
                                                    {{ $course->id === $selectedCourse->id ? 'selected="selected"' : '' }}">
                                                    {{ $course->title }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-6 col-xxl-5">
                                        <label for="chapterTitle" class="form-label">Chapter Title <span
                                                class="text-danger fw-bold">*</span></label>
                                        <input type="text" class="form-control" id="chapterTitle" name="title"
                                            value="{{ old('title') }}" placeholder="Enter chapter title" />
                                        @error('title')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <div class="col-6 col-xxl-2">
                                        <label for="chapterSequence" class="form-label">Serial Number <span
                                                class="text-danger fw-bold">*</span></label>
                                        <input type="text" class="form-control" id="chapterSequence" name="serial_number"
                                            value="{{ $course->chapters()->count() + 1 }}" />
                                        @error('serial_number')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>


                <div class="row">
                    <div class="col-md-12 mb-1">
                        <div class="card">
                            <div class="card-body">
                                <h6 class="m-0 p-0">
                                    {{ __('Contents') }}</h6>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-body">
                                <div class="startContentBody">
                                    <div id="contentsWrapper">
                                        @php $contentCounter = 1; @endphp
                                        @foreach (old('contents', [1 => ['title' => '', 'serial_number' => 1]]) as $key => $content)
                                            <div id="content{{ $key }}"
                                                class="row d-flex align-items-center border-primary border-2 p-3 rounded-4 mb-5 content-item">
                                                <div class="col-6 mb-3">
                                                    <div class="d-flex align-items-center gap-3">
                                                        {{-- <div class="forwardable">
                                                            <div class="form-check">
                                                                <input
                                                                    name="contents[{{ $key }}][is_forwardable]"
                                                                    class="form-check-input"
                                                                    id="isForwardable{{ $key }}" type="checkbox"
                                                                    {{ old("contents.$key.is_forwardable") ? 'checked' : '' }}>
                                                                <label class="form-check-label"
                                                                    for="isForwardable{{ $key }}">Forwardable</label>
                                                            </div>
                                                        </div> --}}
                                                        <div class="isFree">
                                                            <div class="form-check">
                                                                <input name="contents[{{ $key }}][is_free]"
                                                                    class="form-check-input" type="checkbox"
                                                                    {{ old("contents.$key.is_free") ? 'checked' : '' }}
                                                                    id="isFree{{ $key }}">
                                                                <label class="form-check-label"
                                                                    for="isFree{{ $key }}">Free
                                                                    Content</label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-6 mb-3 d-flex justify-content-end align-items-center">
                                                    @if (old("contents.$key.title"))
                                                        <button type="button" class="btn btn-outline-danger"
                                                            onclick="removeContentItem({{ $key }})">
                                                            Remove Content -</button>
                                                    @endif
                                                </div>
                                                <div class="col-3">
                                                    <div class="">
                                                        <label class="form-label">Content Title</label>
                                                        <input type="text" name="contents[{{ $key }}][title]"
                                                            class="form-control" placeholder="Enter content title"
                                                            value="{{ old("contents.$key.title") }}">
                                                    </div>
                                                </div>

                                                <div class="col-1">
                                                    <div class="">
                                                        <label class="form-label">Sequence</label>
                                                        <input type="number" min="1"
                                                            name="contents[{{ $key }}][serial_number]"
                                                            class="form-control"
                                                            value="{{ old("contents.$key.serial_number", $key) }}">
                                                    </div>
                                                </div>

                                                <div class="col-2">
                                                    <div class="">
                                                        <label for="fileInput" class="form-label">Select File
                                                            Type</label>
                                                        <select class="form-select form-control videoTypeSelect"
                                                            name="contents[{{ $key }}][video_type]"
                                                            data-key="{{ $key }}">
                                                            <option value="0">----Select Option----</option>
                                                            <option value="upload" selected
                                                                {{ old("contents.$key.video_type") == 'upload' ? 'selected' : '' }}>
                                                                Upload Files</option>
                                                            <option value="link"
                                                                {{ old("contents.$key.video_type") == 'link' ? 'selected' : '' }}>
                                                                Cloud Link</option>
                                                        </select>
                                                    </div>
                                                </div>

                                                <div class="col-6 {{ old("contents.$key.video_type") == 'upload' ? '' : '' }}"
                                                    id="uploadSection{{ $key }}">
                                                    <div class="">
                                                        <h4 class="form-label">Upload File (Document, Audio, Video, Image)
                                                        </h4>
                                                        <label for="formFileUpload"
                                                            class="w-100 border rounded-3 d-flex align-items-center gap-2">
                                                            <div class="d-flex justify-content-center align-items-center gap-2 p-3"
                                                                style="width: 160px; background-color: #EDEEF1">
                                                                <span>Choose a file</span>
                                                                <img src="/assets/images/media/file-plus.svg">
                                                            </div>
                                                            <div>
                                                                <span id="fileLinkPreview{{ $key }}"></span>
                                                            </div>
                                                        </label>
                                                        <input id="formFileUpload" class="form-control form-control-lg"
                                                            type="file" accept="*/*"
                                                            name="contents[{{ $key }}][media]"
                                                            onchange="document.getElementById('fileLinkPreview{{ $key }}').innerHTML = window.URL.createObjectURL(this.files[0])"
                                                            hidden />
                                                    </div>
                                                </div>

                                                <div class="col-3 {{ old("contents.$key.video_type") == 'link' ? '' : 'hidden' }}"
                                                    id="linkSection{{ $key }}">
                                                    <div class="">
                                                        <label for="fileInput" class="form-label">Upload Link (only
                                                            embed link)</label>
                                                        <input name="contents[{{ $key }}][link]" type="text"
                                                            class="form-control" value="{{ old("contents.$key.link") }}"
                                                            placeholder="Enter Youtube Embed Video Links">
                                                    </div>
                                                </div>

                                                <div class="col-3 {{ old("contents.$key.video_type") == 'link' ? '' : 'hidden' }}"
                                                    id="duration{{ $key }}">
                                                    <div class="">
                                                        <label for="fileInput" class="form-label">Set Duration</label>
                                                        <input name="contents[{{ $key }}][duration]"
                                                            type="number" class="form-control"
                                                            value="{{ old("contents.$key.duration") }}"
                                                            placeholder="Enter Link Duration">
                                                    </div>
                                                </div>

                                            </div>
                                            @php $contentCounter++; @endphp
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12 mt-2">
                        <div class="card">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col-md-6">
                                        <button type="button" onclick="handleSubmit()"
                                            class="btn btn-primary px-5 py-2">{{ __('Create') }}</button>
                                    </div>
                                    <div class="col-md-6 d-flex justify-content-end">
                                        <button type="button" class="btn btn-outline-primary"
                                            onclick="addContentItem(0)">Add New Content Item +</button>
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
        var contentCounter = 2;

        function addContentItem() {
            var contentRow = `
                                <div id="content${contentCounter}" class="row d-flex align-items-center border border-primary border-2 p-3 rounded-4 mb-5 content-item">
                                            <div class="col-6 my-3">
                                                <div class="d-flex align-items-center gap-3">
                                                    <div class="isFree">
                                                        <div class="form-check">
                                                            <input name="contents[${contentCounter}][is_free]"
                                                                class="form-check-input" type="checkbox"
                                                                id="isFree${contentCounter}">
                                                            <label class="form-check-label"
                                                                for="isFree${contentCounter}">Free
                                                                Content</label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-6 my-3 d-flex justify-content-end align-items-center">
                                                    <button type="button" class="btn btn-outline-danger"
                                                        onclick="removeContentItem(${contentCounter})">
                                                        Remove Content -</button>
                                            </div>
                                            <div class="col-3">
                                                <div class="">
                                                    <label class="form-label">Content Title</label>
                                                    <input type="text"
                                                        name="contents[${contentCounter}][title]" class="form-control"
                                                        placeholder="Enter content title"
                                                        value="{{ old("contents.${contentCounter}.title") }}">
                                                </div>
                                            </div>

                                            <div class="col-1">
                                                <div class="">
                                                    <label class="form-label">Sequence</label>
                                                    <input type="number" min="1" value="${contentCounter}"
                                                        name="contents[${contentCounter}][serial_number]"
                                                        class="form-control">
                                                </div>
                                            </div>

                                            <div class="col-2">
                                                <div class="">
                                                    <label for="fileInput" class="form-label">Select File
                                                        Type</label>
                                                    <select class="form-select form-control videoTypeSelect"
                                                        name="contents[${contentCounter}][video_type]"
                                                        data-key="${contentCounter}">
                                                        <option value="0">----Select Option----</option>
                                                        <option value="upload" selected>
                                                            Upload Files</option>
                                                        <option value="link">
                                                            Cloud Link</option>
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="col-6"
                                                id="uploadSection${contentCounter}">
                                                <div class="">
                                                    <h4 class="form-label">Upload File (Document, Audio, Video, Image)
                                                    </h4>
                                                    <label for="formFileUpload${contentCounter}"
                                                        class="w-100 border rounded-3 d-flex align-items-center gap-2">
                                                        <div class="d-flex justify-content-center align-items-center gap-2 p-3"
                                                            style="width: 160px; background-color: #EDEEF1">
                                                            <span>Choose a file</span>
                                                            <img src="/assets/images/media/file-plus.svg">
                                                        </div>
                                                        <div>
                                                            <span id="fileLinkPreview${contentCounter}"></span>
                                                        </div>
                                                    </label>
                                                    <input id="formFileUpload${contentCounter}" class="form-control form-control-lg"
                                                        type="file" accept="*/*"
                                                        name="contents[${contentCounter}][media]"
                                                        onchange="document.getElementById('fileLinkPreview${contentCounter}').innerHTML = window.URL.createObjectURL(this.files[0])"
                                                        hidden />
                                                </div>
                                            </div>

                                            <div class="col-3 {{ old("contents.${contentCounter}.video_type") == 'link' ? '' : 'hidden' }}"
                                                id="linkSection${contentCounter}">
                                                <div class="">
                                                    <label for="fileInput" class="form-label">Upload Link (only
                                                        embed link)</label>
                                                    <input name="contents[${contentCounter}][link]" type="text"
                                                        class="form-control" value="{{ old("contents.${contentCounter}.link") }}"
                                                        placeholder="Enter Youtube Embed Video Links">
                                                </div>
                                            </div>

                                            <div class="col-3 {{ old("contents.${contentCounter}.video_type") == 'link' ? '' : 'hidden' }}"
                                                id="duration${contentCounter}">
                                                <div class="">
                                                    <label for="fileInput" class="form-label">Set Duration</label>
                                                    <input name="contents[${contentCounter}][duration]" type="number"
                                                        class="form-control" value="{{ old("contents.${contentCounter}.duration") }}"
                                                        placeholder="Enter Link Duration">
                                                </div>
                                            </div>

                                        </div>
            `;

            $('#contentsWrapper').append(contentRow);

            $(`#content${contentCounter} #videoTypeSelect`).on('change', function() {
                const selectedValue = $(this).val();
                const counter = $(this).data('counter');

                // Hide all sections
                $(`#uploadSection${counter}`).addClass('hidden');
                $(`#linkSection${counter}`).addClass('hidden');
                $(`#duration${counter}`).addClass('hidden');

                // Show the selected section
                if (selectedValue === 'upload') {
                    $(`#uploadSection${counter}`).removeClass('hidden');
                    $(`#linkSection${counter}`).find(`input[name="contents[${counter}][link]"]`).val('');
                    $(`#duration${counter}`).find(`input[name="contents[${counter}][duration]"]`).val('');
                } else if (selectedValue === 'link') {
                    $(`#linkSection${counter}`).removeClass('hidden');
                    $(`#duration${counter}`).removeClass('hidden');
                    $(`#uploadSection${counter}`).find(`input[name="contents[${counter}][media]"]`).val('');
                    document.getElementById(`fileLinkPreview${counter}`).innerHTML = '';
                }
            });

            ++contentCounter;
        }

        function removeContentItem(elementNumber) {
            $(`#content${elementNumber}`).remove();
        }
    </script>


    <script>
        // Event delegation for videoTypeSelect changes
        document.addEventListener('DOMContentLoaded', function() {
            const contentsWrapper = document.getElementById('contentsWrapper');

            contentsWrapper.addEventListener('change', function(event) {
                if (event.target.classList.contains('videoTypeSelect')) {
                    const selectedValue = event.target.value;
                    const key = event.target.getAttribute('data-key');

                    // Hide all sections

                    // Show the appropriate section based on the selection
                    document.getElementById(`uploadSection${key}`).classList.add('hidden');
                    document.getElementById(`linkSection${key}`).classList.add('hidden');
                    document.getElementById(`duration${key}`).classList.add('hidden');

                    // Show the appropriate section based on the selection
                    if (selectedValue === 'upload') {
                        document.getElementById(`uploadSection${key}`).classList.remove('hidden');
                        $(`#linkSection${key}`).find(`input[name="contents[${key}][link]"]`).val('');
                        $(`#duration${key}`).find(`input[name="contents[${key}][duration]"]`).val('');
                    } else if (selectedValue === 'link') {
                        document.getElementById(`linkSection${key}`).classList.remove('hidden');
                        document.getElementById(`duration${key}`).classList.remove('hidden');

                        $(`#uploadSection${key}`).find(`input[name="contents[${key}][media]"]`).val(null);
                        document.getElementById(`fileLinkPreview${key}`).innerHTML = '';

                    }
                }
            });
        });
    </script>

    <script>
        let totalContentCount = 0;
        let contentUploaded = 0;
        let chapterID = null;
        let isLoading = false;
        let redirect = '';
        let someError = false;

        let progress = document.querySelector('.progress');
        let progressBar = document.querySelector('.progress-bar');
        let ariaValueNow = progress.getAttribute('aria-valuenow');
        let ariaValueMax = progress.getAttribute('aria-valuemax');
        let progressLoader = document.getElementById('progressLoader');
        let totalUpload = document.getElementById('totalupload');
        let totalItem = document.getElementById('totalitem');



        function handleSubmit() {
            progressLoader.style.display = 'flex';
            let contents = [];

            let items = document.querySelectorAll('.content-item');
            totalContentCount = items?.length || 0;

            totalItem.innerHTML = totalContentCount;
            // totalUpload.innerHTML = contentUploaded;

            items.forEach((content, index) => {

                let keyInput = content.querySelector(`input[name^="contents["]`);
                if (!keyInput) return;
                let key = keyInput.name.match(/\[([0-9]+)\]/)[1];


                let title = content.querySelector(`input[name="contents[${key}][title]"]`)?.value || "";
                let serialNumber = content.querySelector(`input[name="contents[${key}][serial_number]"]`)?.value ||
                    1;
                let isForwardable = content.querySelector(`input[name="contents[${key}][is_forwardable]"]`)
                    ?.checked ? 1 : 0;
                let isFree = content.querySelector(`input[name="contents[${key}][is_free]"]`)?.checked ? 1 : 0;
                let link = content.querySelector(`input[name="contents[${key}][link]"]`)?.value || "";
                let duration = content.querySelector(`input[name="contents[${key}][duration]"]`)?.value || "";
                let media = content.querySelector(`input[name="contents[${key}][media]"]`)?.files[0] || null;


                let contentData = {
                    title: title,
                    serial_number: serialNumber,
                    is_forwardable: isForwardable,
                    is_free: isFree,
                    link: link,
                    duration: duration,
                    media: media
                };

                contents.push(contentData);

            });

            let hasInvalidContent = contents.some(content =>
                (content.media === null && content.link === "") || content.title === ""
            );

            if (hasInvalidContent) {
                progressLoader.style.display = "none";
                isLoading = false;
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
                    icon: "error",
                    title: "Please make sure all your contents have a title and either a file or a link attached.",
                });
                return;
            }

            uploadContent(contents, 0)
        }


        function uploadContent(filterData, index) {
            if (index >= filterData.length) {
                progressLoader.style.display = "none";
                isLoading = false;
                localStorage.setItem('chapterSuccess', 'Chapter Created Successfully');
                window.location.href = redirect;
                return;
            }

            let formProcessData = new FormData();
            let content = filterData[index];

            let course_id = document.querySelector("select[name='course_id']").value
            let title = document.querySelector("input[name='title']").value
            let serial_number = document.querySelector("input[name='serial_number']").value

            formProcessData.append("course_id", course_id);
            formProcessData.append("title", title);
            formProcessData.append("serial_number", serial_number);
            formProcessData.append(`contents[${index}][title]`, content.title);
            formProcessData.append(`contents[${index}][serial_number]`, content.serial_number);
            formProcessData.append(`contents[${index}][is_forwardable]`, content.is_forwardable);
            formProcessData.append(`contents[${index}][is_free]`, content.is_free);
            formProcessData.append(`contents[${index}][link]`, content.link);
            formProcessData.append(`contents[${index}][duration]`, content.duration);
            formProcessData.append(`chapter_id`, chapterID);

            if (content.media) {
                formProcessData.append(`contents[${index}][media]`, content.media);
            }

            if (index === 0) {
                progressBar.setAttribute('aria-valuenow', 0);
                progressBar.setAttribute('aria-valuemax', totalContentCount);
            }

            isLoading = true;


            $.ajax({
                url: "{{ route('chapter.store') }}",
                type: 'POST',
                data: formProcessData,
                contentType: false,
                processData: false,
                headers: {
                    'X-CSRF-TOKEN': "{{ csrf_token() }}"
                },
                success: (response) => {
                    let chapter = response.data.chapter;
                    chapterID = chapter.id;
                    isLoading = false;
                    redirect = response.data.redirect;

                    contentUploaded += 1;
                    totalUpload.innerHTML = contentUploaded;

                    let percentComplete = Math.floor((contentUploaded / totalContentCount) * 100);
                    progressBar.style.width = `${percentComplete}%`;
                    progressBar.setAttribute('aria-valuenow', percentComplete);
                    progressBar.innerHTML = `${percentComplete}%`;

                    if (contentUploaded - 1 <= totalContentCount) {
                        uploadContent(filterData, index + 1);
                    }
                },
                error: (xhr, textStatus, errorMessage) => {
                    let responseError = xhr.responseJSON?.message || 'Something Went Wrong';
                    progressLoader.style.display = "none";
                    isLoading = false;
                    filterData = [];
                    contentUploaded = 0;

                    // if (responseError.incluedes('Attempt to read property "id" on null')) {
                    //     responseError = 'Please Attach a file or link to the content';
                    // }
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
                        icon: "error",
                        title: `${responseError}`,
                    });
                }
            });

        }
    </script>
@endpush
