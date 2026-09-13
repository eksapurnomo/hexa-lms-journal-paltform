@extends('layouts.app')

@section('title', $app_setting['name'] . ' | '. __('Category Edit'))

@section('content')
    <!-- ****Body-Section***** -->
    <div class="app-main-outer">
        <div class="app-main-inner">
            <div class="page-title-actions px-3 d-flex">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }}</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('category.index') }}">{{ __('Category') }}</a></li>
                        <li class="breadcrumb-item active" aria-current="page">{{ __('Edit') }}</li>
                    </ol>
                </nav>
            </div>
            <div class="row" id="deleteTableItem">
                <div class="col-md-12">
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="m-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    <div class="main-card card d-flex h-100 flex-column">
                        <div class="card-body">
                            <h5 class="card-title py-2">{{ __('Edit Category') }}</h5>
                            <form action="{{ route('category.update', $category->id) }}" method="POST"
                                enctype="multipart/form-data">
                                @csrf @method('PUT')
                                <div class="row">
                                    <div class="col-3">
                                        <div class="mb-3">
                                            <label for="titleInput" class="form-label">{{ __('Category Title') }}</label>
                                            <input type="text" required name="title" value="{{ $category->title }}"
                                                class="form-control" id="titleInput">
                                        </div>
                                    </div>

                                    <div class="col-12">
                                        <div class="mb-3">
                                            <label for="exampleInputFile">{{ __('Category Icon') }} (JPG, JPEG, PNG)</label>
                                            <img src="{{ $category->imagePath }}" class="d-block mb-3" alt="Current image"
                                                height="150px">
                                            <label for="imageInput" class="form-label">
                                                {{ __('Select category icon') }}
                                            </label>
                                            <div class="input-group">
                                                <div class="custom-file">
                                                    <input name="media" type="file"
                                                        class="custom-file-input form-control" id="imageInput">
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-4">
                                        <div class="mb-3">
                                            <label for="colorInput" class="form-label">
                                                {{ __('Choose a color for category background') }}
                                            </label>
                                            <input name="color" type="color" class="form-control form-control-color"
                                                id="colorInput" value="{{ $category->color }}" title="{{ __('Choose your color') }}">
                                        </div>
                                    </div>

                                    <div class="col-12">
                                        <div class="mb-3">
                                            <div class="form-check">
                                                <input id="featuredInput" name="is_featured" class="form-check-input"
                                                    type="checkbox" @if ($category->is_featured) checked @endif>
                                                <label for="featuredInput" class="form-check-label">
                                                    {{ __('Feature on Homepage') }}
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <button type="submit" class="btn bgBlue btn-dipBlue">{{ __('Update') }}</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ****End-Body-Section**** -->
    </div>
@endsection
