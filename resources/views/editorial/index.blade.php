@extends('layouts.app')

@section('title', $app_setting['name'] . ' | '.__('Editorial Desk'))

@section('content')
    <div class="app-main-outer">
        <div class="app-main-inner" id="editorial-app">
            <!-- Vue Editorial Desk Application Mount Point -->
        </div>
    </div>
@endsection

@push('scripts')
    @vite('resources/js/editorial.js')
@endpush
