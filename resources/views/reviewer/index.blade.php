@extends('layouts.app')

@section('title', $app_setting['name'] . ' | ' . __('Reviewer Desk'))

@section('content')
    <div class="app-main-outer">
        <div class="app-main-inner" id="reviewer-app">
            <!-- Vue Reviewer Desk Application Mount Point -->
        </div>
    </div>
@endsection

@push('scripts')
    @vite('resources/js/reviewer.js')
@endpush
