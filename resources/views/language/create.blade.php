@extends('layouts.app')

@section('title', $app_setting['name'] . ' | ' . __('Language Create'))

@section('content')
    <div class="app-main-outer">
        <div class="app-main-inner">

            <div class="container-fluid my-4">
                <div class="row">
                    <div class="col-lg-7 mt-2 mx-auto ">
                        <form action="{{ route('language.store') }}" method="POST">
                            @csrf
                            <div class="card border-0 shadow-sm">
                                <div class="card-header">
                                    <h3 class="m-0">{{ __('Create New Language') }}</h3>
                                </div>
                                <div class="card-body">
                                    <div>
                                        <input class="form-control" type="text" name="title" label="Title"
                                            placeholder="{{ __('title') }}" value="" />
                                        @error('title')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <div class="mt-3">
                                        <label class="mb-0">
                                            {{ __('Short Name') }} <small>
                                                ({{ __('only allow English characters') }})</small>
                                        </label>
                                        <input name="name" oninput="this.value=this.value.replace(/[^a-z]/gi,'')"
                                            class="form-control" placeholder="{{ __('Exm: bn') }}" autocomplete="off" />
                                        @error('name')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="card-footer d-flex justify-content-between flex-wrap gap-2 ">
                                    <a href="{{ route('language.index') }}" class="btn btn-danger px-5">
                                        {{ __('Back') }}
                                        <i class="fa-solid fa-circle-xmark"></i>
                                    </a>
                                    <button type="submit" class="btn btn-success px-5">
                                        {{ __('Submit') }}
                                        <i class="fa-solid fa-thumbs-up"></i>
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection
