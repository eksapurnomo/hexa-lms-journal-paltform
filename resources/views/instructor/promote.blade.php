@extends('layouts.app')

@section('title', $app_setting['name'] . ' | '.__('Instructor Create'))

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
                            <h5 class="card-title py-2">{{ __('Promote to Instructor') }}</h5>
                            <form action="{{ route('instructor.migrate', $user->id) }}" method="POST"
                                enctype="multipart/form-data">
                                @csrf
                                <div class="row">
                                    <div class="col-12">
                                        <h3>{{ __('User') }}: {{ $user->name }}</h3>
                                    </div>

                                    <div class="col-4">
                                        <div class="mb-3">
                                            <label for="titleInput" class="form-label">{{ __('Instructor Title') }}</label>
                                            <input type="text" name="title" required class="form-control"
                                                id="titleInput" placeholder="{{ __('Enter instructor title') }}">
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
