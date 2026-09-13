@extends('layouts.app')

@section('title', $app_setting['name'] . ' | ' . __('Course Create'))

@push('styles')
    <style>
        .ck-editor__editable {
            min-height: 140px;
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
                        <li class="breadcrumb-item"><a href="{{ route('course.index') }}">{{ __('Course') }}</a></li>
                        <li class="breadcrumb-item active" aria-current="page">{{ __('Create') }}</li>
                    </ol>
                </nav>
            </div>

            <form action="{{ route('course.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <h3 class="m-0 p-0">
                                    {{ __('Create a New Course') }}</h3>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row my-3">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <div class="row align-items-start">
                                    <div class="col-md-6 border-end">
                                        <div class="mb-3">
                                            <img id="courseImagePreview" src="{{ asset('media/demoimage.png') }}"
                                                class="w-100" style="height: 380px; border-radius:1rem; object-fit: cover">
                                        </div>
                                        <div>
                                            <h4 class="form-label">{{ __('Thumbnail') }} (JPG, JPEG, PNG)*</h4>
                                            <label for="formFileImage" class="w-100 border rounded-3">
                                                <div class="d-flex justify-content-center align-items-center gap-2 p-3"
                                                    style="width: 160px; background-color: #EDEEF1">
                                                    <span>
                                                        {{ __('Choose a file') }}
                                                    </span>
                                                    <img src="/assets/images/media/file-plus.svg">
                                                </div>
                                            </label>
                                            <input name="media" class="form-control form-control-lg" id="formFileImage"
                                                type="file" hidden
                                                onchange="document.getElementById('courseImagePreview').src = window.URL.createObjectURL(this.files[0])" />
                                            @error('media')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6 border-start">
                                        <div class="mb-3">
                                            <video id="courseVideoPreview" width="100%" height="375"
                                                class="border rounded-4" controls
                                                poster="{{ asset('media/demovideo.png') }}">
                                                <source src="" type="video/mp4">
                                            </video>
                                        </div>
                                        <div>
                                            <h4 class="form-label">{{ __('Video') }} (MP4, MPEG)</h4>
                                            <label for="formFileVideo" class="w-100 border rounded-3">
                                                <div class="d-flex justify-content-center align-items-center gap-2 p-3"
                                                    style="width: 160px; background-color: #EDEEF1">
                                                    <span>
                                                        {{ __('Choose a file') }}
                                                    </span>
                                                    <img src="/assets/images/media/file-plus.svg">
                                                </div>
                                            </label>
                                            <input id="formFileVideo" class="form-control form-control-lg" type="file"
                                                accept="*/*" name="video" hidden
                                                onchange="document.getElementById('courseVideoPreview').src = window.URL.createObjectURL(this.files[0])" />
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
                                    <div class="col-md-6">
                                        <div class="row">
                                            <div class="col-md-12 mb-3">
                                                <label for="courseTitle" class="form-label">{{ __('Course Title') }}
                                                    <span class="text-danger fw-bold">*</span>
                                                </label>
                                                <input type="text" class="form-control" id="courseTitle" name="title"
                                                    value="{{ old('title') }}" maxlength="80"
                                                    onchange="countCourseTitleChar()"
                                                    placeholder="{{ __('Enter course title') }}" />
                                                <div class="mt-2">
                                                    <strong>{{ __('Characters') }}:
                                                        <span id="charCountTitle">0</span>/80
                                                    </strong>
                                                </div>
                                                @error('title')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>
                                            <div class="col-md-12">
                                                <label for="instructorName" class="form-label">{{ __('Instructor Name') }}
                                                    <span class="text-danger fw-bold">*</span>
                                                </label>
                                                <select id="instructorInput" class="form-select form-control"
                                                    style="width: 100%;" name="instructor_id" aria-hidden="true">
                                                    <option value="0"
                                                        {{ old('instructor_id') == 0 ? 'selected' : '' }}>
                                                        {{ __('Find Instructor ') }}
                                                    </option>
                                                    @foreach ($instructors as $instructor)
                                                        <option value="{{ $instructor->id }}"
                                                            {{ old('instructor_id') == $instructor->id ? 'selected' : '' }}>
                                                            {{ $instructor->user->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                @error('instructor_id')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="row">
                                            <div class="col-md-12 mb-5">
                                                <label for="categoryName" class="form-label">{{ __('Category Name') }}
                                                    <span class="text-danger fw-bold">*</span>
                                                </label>
                                                <select id="categoryInput" class="form-select form-control"
                                                    style="width: 100%;" name="category_id" aria-hidden="true">
                                                    <option value="0"
                                                        {{ old('category_id') == 0 ? 'selected' : '' }}>
                                                        {{ __('Find Category') }}
                                                    </option>
                                                    @foreach ($categories as $category)
                                                        <option value="{{ $category->id }}"
                                                            {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                                            {{ $category->title }}</option>
                                                    @endforeach
                                                </select>
                                                @error('category_id')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>
                                            <div class="col-md-6">
                                                <label for="regularPrice" class="form-label">{{ __('Regular Price') }}
                                                    <span class="text-primary"
                                                        style="font-size: 0.75rem; font-family: monospace;">({{ __('Minimum Price: ') }}{{ config('app.minimum_amount') }})</span>
                                                    <span class="text-danger fw-bold">*</span>
                                                </label>
                                                <input type="text" class="form-control" id="regularPrice"
                                                    name="regular_price" value="{{ old('regular_price') }}"
                                                    placeholder="{{ __('Enter regular price') }}" />
                                                @error('regular_price')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>
                                            <div class="col-md-6">
                                                <label for="offerPrice" class="form-label">
                                                    {{ __('Offer Price') }}
                                                    <span class="text-primary"
                                                        style="font-size: 0.75rem; font-family: monospace;">({{ __('Minimum Price: ') }}{{ config('app.minimum_amount') }})</span>
                                                </label>
                                                <input type="text" class="form-control" id="offerPrice"
                                                    name="price" value="{{ old('price') }}"
                                                    placeholder="{{ __('Enter offer price') }}" />
                                                @error('price')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row my-3">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-body">
                                <div id="descriptionWrapper">
                                    @if (old('description'))
                                        @foreach (old('description') as $key => $description)
                                            <div id="content{{ $key }}">
                                                <div class="row align-items-center">
                                                    <div class="col-md-6">
                                                        <h4 class="m-0 p-0">{{ __('About the Course') }}
                                                            ({{ __('Section') }} {{ $key }})
                                                        </h4>
                                                    </div>
                                                    <div class="col-md-6 d-flex justify-content-end">
                                                        <button type="button" class="btn btn-outline-danger"
                                                            onclick="removeDescriptionItem({{ $key }})" disabled>
                                                            {{ __('Remove Description') }} -
                                                        </button>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-12 mb-3">
                                                        <label for="courseTitle" class="form-label">{{ __('Title') }}
                                                            <span class="text-danger fw-bold">*</span>
                                                        </label>
                                                        <input type="text" class="form-control" id="courseTitle"
                                                            value="{{ old('description.' . $key . '.heading') }}"
                                                            name="description[{{ $key }}][heading]" required
                                                            placeholder="{{ __('Enter title') }}" />
                                                        @error('description.' . $key . '.heading')
                                                            <span class="text-danger">{{ $message }}</span>
                                                        @enderror
                                                    </div>
                                                    <div class="col-md-12 mb-3">
                                                        <label for="instructorName"
                                                            class="form-label">{{ __('Description') }}
                                                            <span class="text-danger fw-bold">*</span>
                                                        </label>
                                                        <textarea class="form-control" id="texteditor{{ $key }}" name="description[{{ $key }}][body]"
                                                            placeholder="{{ __('Enter description') }}">{{ old('description.' . $key . '.body') }}</textarea>
                                                        @error('description.' . $key . '.body')
                                                            <span class="text-danger">{{ $message }}</span>
                                                        @enderror
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    @else
                                        <div id="content1">
                                            <div class="row align-items-center">
                                                <div class="col-md-6">
                                                    <h4 class="m-0 p-0">{{ __('About the Course') }} ({{ __('Section') }}
                                                        1)</h4>
                                                </div>
                                                <div class="col-md-6 d-flex justify-content-end">
                                                    <button type="button" class="btn btn-outline-danger"
                                                        onclick="removeDescriptionItem(1)" disabled>
                                                        {{ __('Remove Description') }} -
                                                    </button>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-12 mb-3">
                                                    <label for="courseTitle" class="form-label">{{ __('Title') }}
                                                        <span class="text-danger fw-bold">*</span>
                                                    </label>
                                                    <input type="text" class="form-control" id="courseTitle"
                                                        name="description[1][heading]"
                                                        placeholder="{{ __('Enter title') }}" />
                                                    @error('description.1.heading')
                                                        <span class="text-danger">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                                <div class="col-md-12 mb-3">
                                                    <label for="instructorName"
                                                        class="form-label">{{ __('Description') }}
                                                        <span class="text-danger fw-bold">*</span>
                                                    </label>
                                                    <textarea class="form-control" id="texteditor1" name="description[1][body]"
                                                        placeholder="{{ __('Enter description') }}"></textarea>
                                                    @error('description.1.body')
                                                        <span class="text-danger">{{ $message }}</span>
                                                    @enderror
                                                </div>

                                            </div>
                                        </div>
                                    @endif
                                </div>
                                <div class="row">
                                    <div class="col-md-12 d-flex justify-content-end">
                                        <button type="button" class="btn btn-outline-primary"
                                            onclick="addDescriptionItem(0)">
                                            {{ __('Add New Description Item') }}+</button>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-check">
                                            <input id="activeInput" name="is_active" class="form-check-input"
                                                type="checkbox">
                                            <label for="activeInput"
                                                class="form-check-label">{{ __('Approve and Publish') }}</label>
                                        </div>
                                    </div>
                                    <div class="col-md-12 my-3">
                                        <button type="submit"
                                            class="btn btn-primary px-5 py-2">{{ __('Create') }}</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </form>

        </div>


        {{-- end div --}}
    </div>
    <!-- ****End-Body-Section**** -->
    </div>


@endsection
@push('scripts')
    <script src="{{ asset('assets/scripts/ckeditor.js') }}"></script>
    <script>
        function ckeditorInit(index) {
            var descriptionWrapper = document.getElementById('descriptionWrapper');
            ClassicEditor
                .create(document.querySelector('#texteditor' + index))
                .catch(error => {
                    console.error(error);
                });
        }

        let oldDescriptions = @json(old('description') ?? []);

        if (oldDescriptions && typeof oldDescriptions === 'object' && Object.keys(oldDescriptions).length > 1) {
            for (let index = 1; index < Object.keys(oldDescriptions).length; index++) {
                ckeditorInit(index + 1);
            }
        } else {
            console.log("No old descriptions found or invalid structure.");
        }

        ckeditorInit(1);

        function addDescriptionItem() {
            var descriptionCounter = descriptionWrapper.childElementCount + 1;
            var descriptionRow = `<div id="content${descriptionCounter}">
                                            <div class="row align-items-center">
                                                <div class="col-md-6">
                                                    <h4 class="m-0 p-0">{{ __('About the Course') }} ({{ __('Section') }} ${descriptionCounter})</h4>
                                                </div>
                                                <div class="col-md-6 d-flex justify-content-end">
                                                    <button type="button" class="btn btn-outline-danger"
                                                        onclick="removeDescriptionItem(${descriptionCounter})">
                                                        {{ __('Remove Description') }} -
                                                        </button>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-12 mb-3">
                                                    <label for="courseTitle" class="form-label">{{ __('Title') }}
                                                        <span class="text-danger fw-bold">*</span>
                                                        </label>
                                                    <input type="text" class="form-control" id="courseTitle"
                                                        name="description[${descriptionCounter}][heading]" />
                                                </div>
                                                <div class="col-md-12 mb-3">
                                                    <label for="instructorName" class="form-label">{{ __('Description') }}
                                                        <span class="text-danger fw-bold">*</span>
                                                        </label>
                                                    <textarea class="form-control" id="texteditor${descriptionCounter}" name="description[${descriptionCounter}][body]"></textarea>
                                                </div>
                                            </div>
                                        </div>`

            $('#descriptionWrapper').append(descriptionRow);

            ckeditorInit(descriptionCounter);

            ++descriptionCounter;
        }

        function removeDescriptionItem(elementNumber) {
            $(`#content${elementNumber}`).remove();
        }
    </script>

    <script>
        // Wait for the DOM to fully load before adding the event listener
        document.addEventListener('DOMContentLoaded', function() {
            // Get references to the input field and character count display
            const titleInput = document.getElementById('courseTitle');
            const charCountDisplay = document.getElementById('charCountTitle');
            // Function to update the character count
            function countCourseTitleChar() {
                charCountDisplay.textContent = titleInput.value.length;
            }
            // Attach the event listener to update count in real time
            titleInput.addEventListener('input', countCourseTitleChar);
        });
    </script>
@endpush
