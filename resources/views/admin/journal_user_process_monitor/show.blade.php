@extends('layouts.app')
@section('title', __('User Process Detail'))

@push('styles')
<style>
    .process-timeline {
        position: relative;
        padding-left: 30px;
        list-style: none;
    }
    .process-timeline::before {
        content: '';
        position: absolute;
        top: 0;
        bottom: 0;
        left: 11px;
        width: 2px;
        background: #e9ecef;
    }
    .process-node {
        position: relative;
        margin-bottom: 20px;
    }
    .process-node::before {
        content: '';
        position: absolute;
        left: -30px;
        top: 4px;
        width: 14px;
        height: 14px;
        border-radius: 50%;
        background: #fff;
        border: 2px solid #adb5bd;
    }
    .process-node.completed::before {
        background: #198754;
        border-color: #198754;
    }
    .process-node.active::before {
        background: #0d6efd;
        border-color: #0d6efd;
        box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.25);
    }
    .process-node.needs_action::before {
        background: #ffc107;
        border-color: #ffc107;
    }
    .process-node.blocked::before {
        background: #dc3545;
        border-color: #dc3545;
    }
    .process-node.na::before {
        background: #e9ecef;
        border-color: #e9ecef;
    }
</style>
@endpush

@section('content')
<div class="app-main-outer">
    <div class="app-main-inner">
        <div class="page-title-actions px-3 d-flex justify-content-between align-items-center mb-4">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }}</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.journal.process-flow.users.index', ['journal_id' => $selectedJournal->id]) }}">{{ __('Journal User Process Monitor') }}</a></li>
                    <li class="breadcrumb-item active" aria-current="page">{{ __('User Process Detail') }}</li>
                </ol>
            </nav>
            <a href="{{ route('admin.journal.process-flow.users.index', ['journal_id' => $selectedJournal->id]) }}" class="btn btn-secondary btn-sm">{{ __('Back to List') }}</a>
        </div>

        <div class="row">
            <div class="col-md-4">
                <div class="card mb-4">
                    <div class="card-header border-bottom-0 pb-0">
                        <h5 class="card-title">{{ __('User Identity') }}</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <strong>{{ __('Name') }}:</strong> <br>
                            {{ $monitoredUser->name }}
                        </div>
                        <div class="mb-3">
                            <strong>{{ __('Email') }}:</strong> <br>
                            {{ $monitoredUser->email }}
                        </div>
                        <div class="mb-3">
                            <strong>{{ __('Institution') }}:</strong> <br>
                            {{ $monitoredUser->academicProfile->institution ?? __('Not Provided') }}
                        </div>
                        <div class="mb-3">
                            <strong>{{ __('Journal') }}:</strong> <br>
                            {{ $selectedJournal->title }}
                        </div>
                        <div>
                            <strong>{{ __('Applicable Roles') }}:</strong> <br>
                            @foreach($roles as $r)
                                <span class="badge bg-secondary">{{ ucfirst($r) }}</span>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-8">
                @foreach($roles as $role)
                    @php
                        $proc = $processes[$role];
                    @endphp
                    <div class="card mb-4">
                        <div class="card-header bg-light d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">{{ ucfirst($role) }} {{ __('Process Flow') }}</h5>
                            <span class="badge bg-primary fs-6">{{ $proc['progress_percent'] }}% ({{ $proc['progress_text'] }})</span>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-info mb-4">
                                <strong>{{ __('Current Process') }}:</strong> {{ $proc['current_process'] }}<br>
                                <strong class="text-danger">{{ __('Blocker') }}:</strong> <span class="text-danger">{{ $proc['current_blocker'] }}</span>
                            </div>

                            <ul class="process-timeline mt-4">
                                @foreach($proc['nodes'] as $node)
                                    @php
                                        $stateClass = '';
                                        if ($node['state'] === '✓ Completed') $stateClass = 'completed';
                                        elseif ($node['state'] === '● Active') $stateClass = 'active';
                                        elseif ($node['state'] === '⚠ Needs Action') $stateClass = 'needs_action';
                                        elseif ($node['state'] === '⛔ Blocked') $stateClass = 'blocked';
                                        elseif ($node['state'] === '— Not Applicable') $stateClass = 'na';
                                    @endphp
                                    <li class="process-node {{ $stateClass }}">
                                        <div class="fw-bold">{{ $node['name'] }}</div>
                                        <div class="text-muted small">{{ $node['state'] }}</div>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection
