@extends('layouts.app')
@section('title', __('Journal Process Flow'))

@section('content')
<div class="app-main-outer">
    <div class="app-main-inner">
        <div class="page-title-actions px-3 d-flex justify-content-between align-items-center mb-4">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }}</a></li>
                    <li class="breadcrumb-item active" aria-current="page">{{ __('Journal Process Flow') }}</li>
                </ol>
            </nav>
        </div>
        <div class="page-header">
        <div class="page-title">
            <h4>{{ __('Journal Process Flow') }}</h4>
            <h6>{{ __('Track journal membership, verification, editorial and peer review progress.') }}</h6>
        </div>
        @if(isset($selectedJournal))
        <div class="page-btn">
            <a href="{{ route('admin.journal.process-flow.users.index', ['journal_id' => $selectedJournal->id]) }}" class="btn btn-added">
                <img src="{{ asset('assets/img/icons/eye.svg') }}" alt="img" class="me-1"> {{ __('View User Monitor') }}
            </a>
        </div>
        @endif
    </div>

    @if($journals->isEmpty())
        <div class="alert alert-warning">
            {{ __('No journals available or you are not authorized to view any journal process flows.') }}
        </div>
    @else
        <div class="card">
            <div class="card-body">
                <form action="{{ route('admin.journal.process-flow') }}" method="GET" class="mb-4 d-flex align-items-center">
                    <label for="journal_id" class="me-2 fw-bold">{{ __('Select Journal:') }}</label>
                    <select name="journal_id" id="journal_id" class="form-select w-auto" onchange="this.form.submit()">
                        @foreach($journals as $journal)
                            <option value="{{ $journal->id }}" {{ $selectedJournal->id == $journal->id ? 'selected' : '' }}>
                                {{ $journal->selector_label }}
                            </option>
                        @endforeach
                    </select>
                </form>

                <div class="alert alert-primary mb-5">
                    <h5 class="mb-1 fw-bold">{{ __('Current Process:') }} {{ $currentProcess['process'] }}</h5>
                    <p class="mb-0 text-muted">{{ __('Current Blocker:') }} {{ $currentProcess['blocker'] }}</p>
                </div>

                <div class="row g-4">
                    <!-- Membership -->
                    <div class="col-md-4 col-sm-6">
                        <div class="card bg-light border">
                            <div class="card-header border-bottom-0 pb-0">
                                <h5>① {{ __('MEMBERSHIP') }}</h5>
                            </div>
                            <div class="card-body">
                                <ul class="list-unstyled mb-0">
                                    <li class="d-flex justify-content-between mb-2">
                                        <span>{{ __('Active') }}</span>
                                        <strong>{{ $stats['membership']['active'] }}</strong>
                                    </li>
                                    <li class="d-flex justify-content-between mb-2">
                                        <span class="text-muted">{{ __('Pending') }}</span>
                                        <strong>{{ $stats['membership']['pending'] }}</strong>
                                    </li>
                                    <li class="d-flex justify-content-between">
                                        <span class="text-danger">{{ __('Suspended/Revoked') }}</span>
                                        <strong>{{ $stats['membership']['suspended'] + $stats['membership']['revoked'] }}</strong>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Verification -->
                    <div class="col-md-4 col-sm-6">
                        <div class="card bg-light border">
                            <div class="card-header border-bottom-0 pb-0">
                                <h5>② {{ __('VERIFICATION') }}</h5>
                            </div>
                            <div class="card-body">
                                <ul class="list-unstyled mb-0">
                                    <li class="d-flex justify-content-between mb-2">
                                        <span>{{ __('Approved') }}</span>
                                        <strong>{{ $stats['verification']['approved'] }}</strong>
                                    </li>
                                    <li class="d-flex justify-content-between mb-2">
                                        <span class="text-warning">{{ __('Under Review') }}</span>
                                        <strong>{{ $stats['verification']['under_review'] }}</strong>
                                    </li>
                                    <li class="d-flex justify-content-between mb-2">
                                        <span class="text-info">{{ __('Needs Revision') }}</span>
                                        <strong>{{ $stats['verification']['needs_revision'] }}</strong>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Editorial -->
                    <div class="col-md-4 col-sm-6">
                        <div class="card bg-light border">
                            <div class="card-header border-bottom-0 pb-0">
                                <h5>③ {{ __('EDITORIAL') }}</h5>
                            </div>
                            <div class="card-body">
                                <ul class="list-unstyled mb-0">
                                    <li class="d-flex justify-content-between mb-2">
                                        <span>{{ __('Assigned') }}</span>
                                        <strong>{{ $stats['editorial']['assigned'] }}</strong>
                                    </li>
                                    <li class="d-flex justify-content-between mb-2">
                                        <span class="text-warning">{{ __('Unassigned') }}</span>
                                        <strong>{{ $stats['editorial']['unassigned'] }}</strong>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Reviewer Selection -->
                    <div class="col-md-4 col-sm-6">
                        <div class="card bg-light border">
                            <div class="card-header border-bottom-0 pb-0">
                                <h5>④ {{ __('REVIEWER SELECTION') }}</h5>
                            </div>
                            <div class="card-body">
                                <ul class="list-unstyled mb-0">
                                    <li class="d-flex justify-content-between mb-2">
                                        <span>{{ __('Rounds') }}</span>
                                        <strong>{{ $stats['reviewerSelection']['rounds'] }}</strong>
                                    </li>
                                    <li class="d-flex justify-content-between mb-2">
                                        <span class="text-warning">{{ __('Awaiting') }}</span>
                                        <strong>{{ $stats['reviewerSelection']['awaiting'] }}</strong>
                                    </li>
                                    <li class="d-flex justify-content-between mb-2">
                                        <span>{{ __('Assigned') }}</span>
                                        <strong>{{ $stats['reviewerSelection']['assigned'] }}</strong>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Peer Review -->
                    <div class="col-md-4 col-sm-6">
                        <div class="card bg-light border">
                            <div class="card-header border-bottom-0 pb-0">
                                <h5>⑤ {{ __('PEER REVIEW') }}</h5>
                            </div>
                            <div class="card-body">
                                <ul class="list-unstyled mb-0">
                                    <li class="d-flex justify-content-between mb-2">
                                        <span>{{ __('Completed') }}</span>
                                        <strong>{{ $stats['peerReview']['completed'] }}</strong>
                                    </li>
                                    <li class="d-flex justify-content-between mb-2">
                                        <span class="text-warning">{{ __('Pending') }}</span>
                                        <strong>{{ $stats['peerReview']['pending'] }}</strong>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Recommendation -->
                    <div class="col-md-4 col-sm-6">
                        <div class="card bg-light border">
                            <div class="card-header border-bottom-0 pb-0">
                                <h5>⑥ {{ __('RECOMMENDATION') }}</h5>
                            </div>
                            <div class="card-body">
                                <ul class="list-unstyled mb-0">
                                    <li class="d-flex justify-content-between mb-2">
                                        <span>{{ __('Submitted') }}</span>
                                        <strong>{{ $stats['recommendation']['submitted'] }}</strong>
                                    </li>
                                    <li class="d-flex justify-content-between mb-2">
                                        <span class="text-warning">{{ __('Pending') }}</span>
                                        <strong>{{ $stats['recommendation']['awaiting'] }}</strong>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Editorial Decision -->
                    <div class="col-md-4 col-sm-6">
                        <div class="card bg-light border">
                            <div class="card-header border-bottom-0 pb-0">
                                <h5>⑦ {{ __('EDITORIAL DECISION') }}</h5>
                            </div>
                            <div class="card-body">
                                <ul class="list-unstyled mb-0">
                                    <li class="d-flex justify-content-between mb-2">
                                        <span>{{ __('Completed') }}</span>
                                        <strong>{{ $stats['decision']['completed'] }}</strong>
                                    </li>
                                    <li class="d-flex justify-content-between mb-2">
                                        <span class="text-warning">{{ __('Pending') }}</span>
                                        <strong>{{ $stats['decision']['pending'] }}</strong>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    @endif
    </div>
</div>
@endsection
