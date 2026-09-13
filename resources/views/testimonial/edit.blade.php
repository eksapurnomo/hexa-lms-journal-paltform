@extends('layouts.app')

@section('title', $app_setting['name'] . ' | '.__('Testimonial List'))


@push('styles')
    <style>
        #full-stars-example-two {

            /* use display:inline-flex to prevent whitespace issues. alternatively, you can put all the children of .rating-group on a single line */
            .rating-group {
                display: inline-flex;
            }

            /* make hover effect work properly in IE */
            .rating__icon {
                pointer-events: none;
                font-size: 25px;
            }

            /* hide radio inputs */
            .rating__input {
                position: absolute !important;
                left: -9999px !important;
            }

            /* hide 'none' input from screenreaders */
            .rating__input--none {
                display: none
            }

            /* set icon padding and size */
            .rating__label {
                cursor: pointer;
                padding: 0 0.1em;
                font-size: 2rem;
            }

            /* set default star color */
            .rating__icon--star {
                color: orange;
            }

            /* if any input is checked, make its following siblings grey */
            .rating__input:checked~.rating__label .rating__icon--star {
                color: #ddd;
            }

            /* make all stars orange on rating group hover */
            .rating-group:hover .rating__label .rating__icon--star {
                color: orange;
            }

            /* make hovered input's following siblings grey on hover */
            .rating__input:hover~.rating__label .rating__icon--star {
                color: #ddd;
            }
        }
    </style>
@endpush

@section('content')

    <div class="app-main-outer">
        <div class="app-main-inner">
            {{-- top --}}
            <div class="page-title-actions px-3 d-flex">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }}</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('testimonial.index') }}">{{ __('Testimionial List') }}</a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">{{ __('Edit') }}</li>
                    </ol>
                </nav>
            </div>


            <form action="{{ route('testimonial.update', $testimonial->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="row">
                    <div class="col-md-12 my-3">
                        <div class="card">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="row">
                                            <div class="col-md-12 mb-3">
                                                <label class="form-label">{{ __('Name') }} <span class="text-danger">*</span></label>
                                                <input type="text" name="name" class="form-control"
                                                    placeholder="{{ __('Enter user name') }}" value="{{ $testimonial->name }}">
                                                @error('name')
                                                    <p class="text-danger my-2">{{ $message }}</p>
                                                @enderror
                                            </div>
                                            <div class="col-md-12 mb-3">
                                                <label class="form-label">{{ __('Designation') }} <span
                                                        class="text-danger">*</span></label>
                                                <input type="text" name="designation" id="designation"
                                                    class="form-control" placeholder="{{ __('Enter user designation') }}"
                                                    value="{{ $testimonial->designation }}" maxlength="60"
                                                    onchange="updatedesignationCharCount()">
                                                <div class="mt-2">
                                                    <strong>{{ __('Characters') }}: <span id="designationCount">0</span>/60</strong>
                                                </div>
                                                @error('designation')
                                                    <p class="text-danger my-2">{{ $message }}</p>
                                                @enderror
                                            </div>
                                            <div class="mb-3">
                                                <div class="col-md-12">
                                                    <label class="form-label">{{ __('Description') }}: <span
                                                            class="text-danger">*</span></label>
                                                    <textarea class="form-control" id="description" name="description" rows="8" placeholder="Enter description"
                                                        maxlength="250" onchange="updateDescriptionCharCount()">{{ $testimonial->description ?? '' }}</textarea>

                                                    <div class="mt-2">
                                                        <strong>{{ __('Characters') }}: <span
                                                                id="descriptionCount">0</span>/250</strong>
                                                    </div>
                                                    @error('description')
                                                        <p class="text-danger my-2">{{ $message }}</p>
                                                    @enderror
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="row">
                                            <div class="col-md-12 mb-3">
                                                <div class="mb-3 d-flex justify-content-center">
                                                    <div style="width: 150px; height:150px; border-radius: 50%;">
                                                        <img id="courseImagePreview" src="{{ $testimonial->mediaPath }}"
                                                            class="w-100 h-100"
                                                            style="border-radius:50%; object-fit: cover">
                                                    </div>
                                                </div>
                                                <h4 class="form-label">{{ __('Picture') }} (JPG, JPEG, PNG)*</h4>
                                                <label for="formFileImage" class="w-100 border rounded-3">
                                                    <div class="d-flex justify-content-center align-items-center gap-2 p-3"
                                                        style="width: 160px; background-color: #EDEEF1">
                                                        <span>{{ __('Choose a file') }}</span>
                                                        <img src="/assets/images/media/file-plus.svg">
                                                    </div>
                                                </label>
                                                <input name="picture" class="form-control form-control-lg"
                                                    id="formFileImage" type="file" hidden
                                                    onchange="document.getElementById('courseImagePreview').src = window.URL.createObjectURL(this.files[0])" />
                                                @error('picture')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>

                                            <div class="col-md-12 mb-3">
                                                <div>
                                                    <label class="form-label">{{ __('Ratings') }} <span
                                                            class="text-danger">*</span></label>

                                                    <div id="full-stars-example-two">
                                                        <div class="rating-group">
                                                            <!-- Loop through 5 stars -->
                                                            @for ($i = 1; $i <= 5; $i++)
                                                                <label aria-label="{{ $i }} star"
                                                                    class="rating__label"
                                                                    for="rating3-{{ $i }}">
                                                                    <span
                                                                        class="rating__icon rating__icon--star fa fa-star">
                                                                        <i
                                                                            class="{{ $testimonial->rating >= $i ? 'fa-solid fa-star' : 'fa-regular fa-star' }}"></i>
                                                                    </span>
                                                                </label>
                                                                <input class="rating__input" name="rating"
                                                                    id="rating3-{{ $i }}"
                                                                    value="{{ $i }}" type="radio"
                                                                    {{ $testimonial->rating == $i ? 'checked' : '' }}>
                                                            @endfor
                                                        </div>
                                                    </div>
                                                    @error('rating')
                                                        <span class="text-danger my-2">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                            </div>

                                            <div class="col-md-12 mt-3 d-flex align-items-center gap-3">
                                                <div class="form-check">
                                                    <input id="activeInput" name="is_active" class="form-check-input"
                                                        type="checkbox" {{ $testimonial->is_active ? 'checked' : '' }}>
                                                    <label for="activeInput" class="form-check-label">{{ __('Publish') }}</label>
                                                </div>
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
                                    <div class="col-md-12 d-flex justify-content-end">
                                        <button type="submit"
                                            class="btn btn-primary px-5 py-2">{{ __('Update') }}</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </form>




            {{-- top --}}

        </div>
    </div>

@endsection


@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const titleInputDesignation = document.getElementById('designation');
            const titleInputDescription = document.getElementById('description');
            const designationcharCountDisplay = document.getElementById('designationCount');
            const descriptioncharCountDisplay = document.getElementById('descriptionCount');

            function updatedesignationCharCount() {
                designationcharCountDisplay.textContent = titleInputDesignation.value.length;
            }

            function updateDescriptionCharCount() {
                descriptioncharCountDisplay.textContent = titleInputDescription.value.length;
            }
            // Attach the event listener to update count in real time
            titleInputDesignation.addEventListener('input', updatedesignationCharCount);
            titleInputDescription.addEventListener('input', updateDescriptionCharCount);
        });
    </script>
@endpush
