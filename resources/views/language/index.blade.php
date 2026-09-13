@extends('layouts.app')

@section('title', $app_setting['name'] . ' | ' . __('Language List'))

@section('content')
    <!-- ****Body-Section***** -->
    <div class="app-main-outer">
        <div class="app-main-inner">

            <div class="row"
                style="height: calc(100vh - 300px); display: flex; align-items: center; justify-content: center;">
                <div class="col-xl-8 col-lg-9 mt-2 mx-auto ">
                    <div class="card border-0 rounded shadow-sm">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h3 class="m-0">{{ __('Languages') }}</h3>

                            <a class="btn btn-primary" href="{{ route('language.create') }}">
                                <i class="fa fa-plus-circle" aria-hidden="true"></i>
                                {{ __('Create New') }}
                            </a>
                        </div>
                    </div>

                    @foreach ($allLanguages as $language)
                        <div class="language-item shadow-sm">
                            <div class="d-flex gap-2 flex-wrap">
                                <div style="min-width: 160px">
                                    <small class="text-black-50 d-block fst-italic" style="line-height: 0.7;">
                                        {{ __('Title') }}
                                    </small>
                                    <strong class="fs-6">{{ $language->title }}</strong>
                                </div>

                                <div>
                                    <small class="text-black-50 d-block fst-italic" style="line-height: 0.7;">
                                        {{ __('Name') }}
                                    </small>
                                    <strong>{{ $language->name }}</strong>
                                </div>
                            </div>
                            <div class="d-flex gap-2 flex-wrap align-items-center">
                                @if ($language->name == config('app.locale'))
                                    <span class="badge bg-light text-black">{{ __('Default') }}</span>
                                @else
                                    <a href="{{ route('language.default', $language->id) }}"
                                        class="circleIcon btn btn-outline-warning btn-sm" title="{{ __('Set Default') }}">
                                        <img src="{{ asset('assets/images/menu/Launguage.svg') }}" alt="default"
                                            loading="lazy" />
                                    </a>
                                @endif
                                <a href="{{ route('language.edit', $language->id) }}"
                                    class="btn btn-outline-primary btn-sm">
                                    <i class="fa fa-edit" aria-hidden="true"></i>
                                </a>

                                @if ($language->name != 'en')
                                    <a class="delete-confirm btn btn-outline-danger btn-sm"
                                        href="{{ route('language.delete', $language->id) }}">
                                        <i class="fa fa-trash"></i>
                                    </a>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $('.delete-confirm').on('click', function(e) {
            e.preventDefault();
            const url = $(this).attr('href');
            Swal.fire({
                title: "{{ __('Are you sure?') }}",
                text: "{{ __('You will not be able to revert this!') }}",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#00B894',
                cancelButtonColor: '#d33',
                confirmButtonText: "{{ __('Yes, delete it!') }}",
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = url;
                }
            })
        });
    </script>
@endpush
