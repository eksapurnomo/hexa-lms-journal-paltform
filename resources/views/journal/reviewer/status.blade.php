@extends('layouts.app')

@section('title', __('Reviewer Application Status'))

@section('content')
    <div class="app-main-outer">
        <div class="app-main-inner">
            <div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            
            @if(session('success'))
                <div class="alert alert-success mb-4">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger mb-4">{{ session('error') }}</div>
            @endif

            <div class="card shadow-sm text-center">
                <div class="card-header bg-white pt-4 border-0">
                    <h4 class="mb-0">Reviewer Application Status</h4>
                </div>
                <div class="card-body p-5">
                    
                    @if($application->status === 'pending')
                        <div class="display-4 text-warning mb-3">
                            <i class="fa fa-clock-o"></i>
                        </div>
                        <h3 class="text-warning mb-3">PENDING</h3>
                        <p class="lead">Your reviewer application for <strong>{{ $application->journal->title }}</strong> is awaiting editorial review.</p>
                        <p class="text-muted">Submitted on {{ $application->created_at->format('M d, Y') }}</p>
                    
                    @elseif($application->status === 'accepted')
                        <div class="display-4 text-success mb-3">
                            <i class="fa fa-check-circle"></i>
                        </div>
                        <h3 class="text-success mb-3">ACCEPTED</h3>
                        <p class="lead">Congratulations! Your reviewer application for <strong>{{ $application->journal->title }}</strong> has been accepted.</p>
                        <p class="text-muted">Reviewer workspace will become available in a later phase.</p>
                        <p class="text-muted small">Reviewed on {{ $application->reviewed_at->format('M d, Y') }}</p>

                    @elseif($application->status === 'denied')
                        <div class="display-4 text-danger mb-3">
                            <i class="fa fa-times-circle"></i>
                        </div>
                        <h3 class="text-danger mb-3">DENIED</h3>
                        <p class="lead">Your reviewer application for <strong>{{ $application->journal->title }}</strong> has been denied.</p>
                        
                        @if($application->denial_reason)
                            <div class="alert alert-secondary mt-4 text-start">
                                <strong>Reason for denial:</strong>
                                <p class="mb-0 mt-2">{{ $application->denial_reason }}</p>
                            </div>
                        @endif
                        <p class="text-muted mt-4 small">Reviewed on {{ $application->reviewed_at->format('M d, Y') }}</p>
                        
                        <div class="mt-4">
                            <a href="{{ route('reviewer.apply') }}" class="btn btn-outline-primary">Submit a New Application</a>
                        </div>
                    @endif

                </div>
            </div>
        </div>
        </div>
    </div>
            </div>
        </div>
    </div>
@endsection
