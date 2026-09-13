@extends('layouts.app')

@section('title', __('Reviewer Application Details'))

@section('content')
<div class="app-main-outer">
    <div class="app-main-inner">
        <div class="page-title-actions px-3 d-flex justify-content-between align-items-center">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }}</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.reviewer_applications.index') }}">{{ __('Reviewer Applications') }}</a></li>
                    <li class="breadcrumb-item active" aria-current="page">{{ __('Details') }}</li>
                </ol>
            </nav>
            <a href="{{ route('admin.reviewer_applications.index') }}" class="btn btn-secondary btn-sm">Back to List</a>
        </div>

        <div class="row">
            <div class="col-md-8">
                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif
                @if(session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif
                @if($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="card mb-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">Application Details: {{ $application->user->name }}</h5>
                    </div>
                    <div class="card-body">
                        
                        <h6 class="border-bottom pb-2 mb-3 text-primary">Target Journal</h6>
                        <p><strong>Journal:</strong> {{ $application->journal->title }}</p>

                        <h6 class="border-bottom pb-2 mb-3 text-primary">Personal & Academic Information</h6>
                        <div class="row">
                            <div class="col-md-6"><p><strong>Email:</strong> {{ $application->user->email }}</p></div>
                            <div class="col-md-6"><p><strong>Affiliation:</strong> {{ $application->affiliation }}</p></div>
                            <div class="col-md-6"><p><strong>Department:</strong> {{ $application->department }}</p></div>
                            <div class="col-md-6"><p><strong>Academic Position:</strong> {{ $application->academic_position }}</p></div>
                        </div>

                        <h6 class="border-bottom pb-2 mb-3 mt-3 text-primary">Academic Profile</h6>
                        <div class="row">
                            <div class="col-md-6"><p><strong>ORCID:</strong> {{ $application->orcid ?: 'Not provided' }}</p></div>
                            <div class="col-md-6"><p><strong>Academic URL:</strong> 
                                @if($application->academic_url)
                                    <a href="{{ $application->academic_url }}" target="_blank">{{ $application->academic_url }}</a>
                                @else
                                    Not provided
                                @endif
                            </p></div>
                        </div>

                        <h6 class="border-bottom pb-2 mb-3 mt-3 text-primary">Expertise</h6>
                        <p><strong>Primary Research Area:</strong> {{ $application->primary_research_area }}</p>
                        <p><strong>Research Keywords:</strong> {{ $application->research_keywords ?: 'Not provided' }}</p>
                        <p><strong>Detailed Expertise:</strong><br> {!! nl2br(e($application->expertise ?: 'Not provided')) !!}</p>

                        <h6 class="border-bottom pb-2 mb-3 mt-3 text-primary">Experience & Availability</h6>
                        <div class="row">
                            <div class="col-md-6"><p><strong>Years of Experience:</strong> {{ $application->years_of_experience }}</p></div>
                            <div class="col-md-6"><p><strong>Max Reviews / Month:</strong> {{ $application->max_reviews_per_month }}</p></div>
                        </div>
                        <p><strong>Available for Review:</strong> 
                            {!! $application->available_for_review ? '<span class="text-success">Yes</span>' : '<span class="text-danger">No</span>' !!}
                        </p>
                        <p><strong>Previous Experience:</strong><br> {!! nl2br(e($application->previous_experience ?: 'Not provided')) !!}</p>

                        <h6 class="border-bottom pb-2 mb-3 mt-3 text-primary">Agreements</h6>
                        <ul class="list-unstyled">
                            <li><i class="fa {{ $application->agreed_confidentiality ? 'fa-check text-success' : 'fa-times text-danger' }}"></i> Agrees to confidentiality</li>
                            <li><i class="fa {{ $application->agreed_conflict_of_interest ? 'fa-check text-success' : 'fa-times text-danger' }}"></i> Agrees to declare conflicts of interest</li>
                            <li><i class="fa {{ $application->agreed_guidelines ? 'fa-check text-success' : 'fa-times text-danger' }}"></i> Agrees to reviewer guidelines</li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">Editorial Decision</h5>
                    </div>
                    <div class="card-body">
                        <p><strong>Status:</strong> 
                            @if($application->status === 'pending')
                                <span class="badge bg-warning text-dark fs-6">Pending</span>
                            @elseif($application->status === 'accepted')
                                <span class="badge bg-success fs-6">Accepted</span>
                            @elseif($application->status === 'denied')
                                <span class="badge bg-danger fs-6">Denied</span>
                            @endif
                        </p>

                        <p><strong>Submitted At:</strong> <br>{{ $application->created_at->format('Y-m-d H:i:s') }}</p>

                        @if($application->status !== 'pending')
                            <hr>
                            <p><strong>Reviewed By:</strong> <br>{{ $application->reviewedBy ? $application->reviewedBy->name : 'Unknown' }}</p>
                            <p><strong>Reviewed At:</strong> <br>{{ $application->reviewed_at ? $application->reviewed_at->format('Y-m-d H:i:s') : 'N/A' }}</p>
                            
                            @if($application->status === 'denied' && $application->denial_reason)
                                <div class="alert alert-secondary mt-3">
                                    <strong>Denial Reason:</strong>
                                    <p class="mb-0 mt-2">{{ $application->denial_reason }}</p>
                                </div>
                            @endif
                        @endif

                        @if($application->status === 'pending')
                            <hr>
                            <form action="{{ route('admin.reviewer_applications.accept', $application->id) }}" method="POST" class="mb-3">
                                @csrf
                                <button type="submit" class="btn btn-success w-100 fw-bold" onclick="return confirm('Are you sure you want to ACCEPT this application?')">ACCEPT APPLICATION</button>
                            </form>

                            <button type="button" class="btn btn-outline-danger w-100 fw-bold" data-bs-toggle="collapse" data-bs-target="#denyForm">DENY APPLICATION</button>
                            
                            <div class="collapse mt-3" id="denyForm">
                                <form action="{{ route('admin.reviewer_applications.deny', $application->id) }}" method="POST">
                                    @csrf
                                    <div class="mb-3">
                                        <label for="denial_reason" class="form-label fw-bold">Reason for Denial <span class="text-danger">*</span></label>
                                        <textarea name="denial_reason" id="denial_reason" class="form-control" rows="4" required placeholder="Provide a reason..."></textarea>
                                    </div>
                                    <div class="d-flex justify-content-between">
                                        <button type="button" class="btn btn-sm btn-secondary" data-bs-toggle="collapse" data-bs-target="#denyForm">Cancel</button>
                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to DENY this application?')">Confirm Denial</button>
                                    </div>
                                </form>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
