@extends('layouts.app')

@section('title', $app_setting['name'] . ' | ' . __('Contact Messages'))


@push('styles')
    <style>
        .message_icon {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            background: #f6f7f9;
            border: 1px solid #efeded;
            margin-bottom: 12px;
        }

        .message_title h4 {
            font-family: Inter, sans-serif;
            font-weight: 600;
            font-size: 20px;
        }

        .delete_message {
            position: absolute;
            top: 10px;
            right: 10px;
            cursor: pointer;
        }

        .message_description {
            margin-top: 15px;
            height: 170px;
        }

        .gridView {
            display: block;
        }

        .listView {
            display: none;
        }
    </style>
@endpush

@section('content')
    <div class="app-main-outer">
        <div class="app-main-inner">
            <!-- ****Body-Section***** -->
            <div class="page-title-actions px-3 d-flex">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }}</a></li>
                        {{-- <li class="breadcrumb-item"><a href="{{ route('course.index') }}">Course</a></li> --}}
                        <li class="breadcrumb-item active" aria-current="page">{{ __('Messages') }}</li>
                    </ol>
                </nav>
            </div>


            <div class="row">
                <div class="col-lg-12 mb-4">
                    <div class="card">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-8">
                                    <h3 class="m-0 p-0">
                                        {{ __('Contact Messages') }}</h3>
                                </div>
                                <div class="col-4">
                                    <div class="btn-group float-end" role="group" aria-label="Basic example">
                                        <button type="button" class="btn btn-outline-secondary active"
                                            onclick="gridView()">
                                            <i class="bi bi-grid"></i>
                                        </button>
                                        <button type="button" class="btn btn-outline-secondary" onclick="listView()">
                                            <i class="bi bi-list"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-12">
                    <div class="row">
                        @forelse ($allcontacts as $contact)
                            <div class="col-lg-4 my-3 gridView">
                                <div class="card position-relative">
                                    <div class="card-body">
                                        @if (!$contact->state)
                                            <div class="mb-3">
                                                <span class="badge bg-warning">
                                                    {{ __('unread') }}
                                                </span>
                                            </div>
                                        @else
                                            <div class="mb-3">
                                                <span class="badge bg-success">
                                                    {{ __('read') }} <i class="bi bi-check-all"></i>
                                                </span>
                                            </div>
                                        @endif
                                        <div class="message_icon">
                                            <img src="{{ asset('assets/images/menu/message.svg') }}" alt="icon">
                                        </div>
                                        <div class="message_title">
                                            <h4>{{ $contact->name }}</h4>
                                        </div>
                                        <div class="message_description mb-3">
                                            <p class="mb-2">{{ __('Subject') }} : <span
                                                    class="fw-bold">{{ $contact->subject }}</span>
                                            </p>
                                            <p class="text-muted">
                                                {{ \Illuminate\Support\Str::limit($contact->message, 120, ' ...') }}
                                            </p>
                                        </div>
                                        <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal"
                                            data-bs-target="#contactDetailsShowModal{{ $contact->id }}"
                                            onclick="readMessage({{ $contact->id }})">{{ __('Read More') }}</button>
                                    </div>
                                    <div class="delete_message">
                                        <a href="{{ route('contact.destroy', $contact->id) }}" data-bs-toggle="tooltip"
                                            data-bs-placement="top" data-bs-custom-class="custom-tooltip"
                                            data-bs-title="{{ __('Delete Message') }}">
                                            <img src="{{ asset('assets/images/icon/trash.svg') }}" alt="icon">
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-12 my-3 listView">
                                <div class="card position-relative">
                                    <div class="card-body d-flex align-items-center justify-content-between"
                                        style="height: 150px;">
                                        <div class="d-flex align-items-center gap-3">
                                            @if (!$contact->state)
                                                <div class="mb-3">
                                                    <span class="badge bg-warning">
                                                        {{ __('unread') }}
                                                    </span>
                                                </div>
                                            @else
                                                <div class="mb-3">
                                                    <span class="badge bg-success">
                                                        {{ __('read') }} <i class="bi bi-check-all"></i>
                                                    </span>
                                                </div>
                                            @endif
                                            <div class="message_icon">
                                                <img src="{{ asset('assets/images/menu/message.svg') }}" alt="icon">
                                            </div>
                                            <div class="message_title">
                                                <h4>{{ $contact->name }}</h4>
                                            </div>
                                        </div>
                                        {{-- <div
                                            class="message_description d-flex flex-column align-items-center justify-content-center m-0">
                                            <div>
                                                <p class="mb-2">Subject : <span
                                                        class="fw-bold">{{ $contact->subject }}</span>
                                                </p>
                                                <p class="text-muted">
                                                    {{ \Illuminate\Support\Str::limit($contact->message, 120, ' ...') }}
                                                </p>
                                            </div>
                                        </div> --}}
                                        <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal"
                                            data-bs-target="#contactDetailsShowModal{{ $contact->id }}"
                                            onclick="readMessage({{ $contact->id }})">{{ __('Read More') }}</button>
                                    </div>
                                    <div class="delete_message">
                                        <a href="{{ route('contact.destroy', $contact->id) }}" data-bs-toggle="tooltip"
                                            data-bs-placement="top" data-bs-custom-class="custom-tooltip"
                                            data-bs-title="{{ __('Delete Message') }}">
                                            <img src="{{ asset('assets/images/icon/trash.svg') }}" alt="icon">
                                        </a>
                                    </div>
                                </div>
                            </div>

                            {{-- modal start --}}

                            <div class="modal fade" id="contactDetailsShowModal{{ $contact->id }}" tabindex="-1"
                                aria-labelledby="exampleModalLabel" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content border-4 border-primary">
                                        <div class="modal-header">
                                            <h1 class="modal-title fs-5 fw-bolder" id="exampleModalLabel">
                                                {{ __('Contact Message') }}
                                            </h1>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                aria-label="Close" onclick="window.location.reload()"></button>
                                        </div>
                                        <div class="modal-body">
                                            <p>{{ __('Name') }} : <span class="fw-bold">{{ $contact->name }}</span>
                                            </p>
                                            <p>{{ __('Email') }} : <span class="fw-bold">{{ $contact->email }}</span>
                                            </p>
                                            <p>{{ __('Subject') }} : <span class="fw-bold">{{ $contact->subject }}</span>
                                            </p>
                                            <p class="fw-bold">{{ __('Message') }} : </p>
                                            <p class="text-muted">
                                                {{ $contact->message }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- modal end --}}

                        @empty
                            <div class="row">
                                <div class="col-12">
                                    <div class="alert alert-primary text-center">
                                        <h5 class="alert-heading m-0 fw-bold text-danger">
                                            {{ __('No information available') }}.</h5>
                                    </div>
                                </div>
                            </div>
                        @endforelse

                    </div>
                </div>
                {{ $allcontacts->links() }}
            </div>
            <!-- ****End-Body-Section***** -->
        </div>
    </div>
@endsection


@push('scripts')
    <script>
        function readMessage(id) {
            $.ajax({
                url: `/admin/contact/show/${id}`,
                type: "get",
                data: {
                    _token: "{{ csrf_token() }}"
                },
                success: function(data) {
                    console.log(data);
                }
            });
        }



        function listView() {
            let gridItems = document.querySelectorAll('.gridView');
            let listItems = document.querySelectorAll('.listView');

            gridItems.forEach(item => {
                item.style.display = 'none';
            });

            listItems.forEach(item => {
                item.style.display = 'block';
            });
            toggleActiveClass('list');
        }

        function gridView() {
            let gridItems = document.querySelectorAll('.gridView');
            let listItems = document.querySelectorAll('.listView');

            gridItems.forEach(item => {
                item.style.display = 'block';
            });

            listItems.forEach(item => {
                item.style.display = 'none';
            });
            toggleActiveClass('grid');
        }


        function toggleActiveClass(view) {
            // Get both buttons
            let gridButton = document.querySelector('button[onclick="gridView()"]');
            let listButton = document.querySelector('button[onclick="listView()"]');

            // Remove active class from both buttons
            gridButton.classList.remove('active');
            listButton.classList.remove('active');

            // Add active class to the corresponding button
            if (view === 'grid') {
                gridButton.classList.add('active');
            } else {
                listButton.classList.add('active');
            }
        }
    </script>
@endpush
