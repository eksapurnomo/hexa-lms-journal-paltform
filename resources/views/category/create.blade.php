@extends('layouts.app')

@section('title', $app_setting['name'] . ' | '. __('Category Create'))

@section('content')
    <!-- ****Body-Section***** -->
    <div class="app-main-outer">
        <div class="app-main-inner">
            <div class="page-title-actions px-3 d-flex">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }}</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('category.index') }}">{{ __('Category') }}</a></li>
                        <li class="breadcrumb-item active" aria-current="page">{{ __('Create') }}</li>
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
                            <h5 class="card-title py-2">{{ __('New Category') }}</h5>
                            <form action="{{ route('category.store') }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <div class="row">
                                    <div class="col-4">
                                        <div class="mb-3">
                                            <label for="titleInput" class="form-label">{{ __('Category Title') }}</label>
                                            <input type="text" name="title" required class="form-control"
                                                id="titleInput" placeholder="{{ __('Enter category title') }}...">
                                        </div>
                                    </div>

                                    <div class="col-4">
                                        <div class="mb-3">
                                            <label for="imageInput" class="form-label">{{ __('Category Icon') }} (JPG, JPEG,
                                                PNG)</label>
                                            <div class="input-group">
                                                <div class="custom-file">
                                                    <input name="media" type="file"
                                                        class="custom-file-input form-control" required id="imageInput">
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
                                                id="colorInput" value="#563d7c" title="{{ __('Choose your color') }}">
                                        </div>
                                    </div>

                                    <div class="col-12">
                                        <div class="mb-3">
                                            <div class="form-check">
                                                <input id="featuredInput" name="is_featured" class="form-check-input"
                                                    type="checkbox">
                                                <label for="featuredInput" class="form-check-label">
                                                    {{ __('Feature on Homepage') }}
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <button type="submit" class="btn bgBlue btn-dipBlue">{{ __('Create') }}</button>
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
