@extends('layouts.app')

@section('title', __('Reviewer Applications'))

@section('content')
<div class="app-main-outer">
    <div class="app-main-inner">
        <div class="page-title-actions px-3 d-flex justify-content-between align-items-center">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }}</a></li>
                    <li class="breadcrumb-item active" aria-current="page">{{ __('Reviewer Applications') }}</li>
                </ol>
            </nav>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="card mb-5">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Reviewer Applications</h5>
                        
                        <div class="btn-group">
                            <a href="{{ route('admin.reviewer_applications.index', ['status' => 'all']) }}" class="btn btn-sm {{ $currentStatus == 'all' ? 'btn-primary' : 'btn-outline-primary' }}">All</a>
                            <a href="{{ route('admin.reviewer_applications.index', ['status' => 'pending']) }}" class="btn btn-sm {{ $currentStatus == 'pending' ? 'btn-primary' : 'btn-outline-primary' }}">Pending</a>
                            <a href="{{ route('admin.reviewer_applications.index', ['status' => 'accepted']) }}" class="btn btn-sm {{ $currentStatus == 'accepted' ? 'btn-primary' : 'btn-outline-primary' }}">Accepted</a>
                            <a href="{{ route('admin.reviewer_applications.index', ['status' => 'denied']) }}" class="btn btn-sm {{ $currentStatus == 'denied' ? 'btn-primary' : 'btn-outline-primary' }}">Denied</a>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Applicant</th>
                                        <th>Journal</th>
                                        <th>Affiliation</th>
                                        <th>Research Area</th>
                                        <th>Status</th>
                                        <th>Submitted</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($applications as $app)
                                        <tr>
                                            <td>
                                                <strong>{{ $app->user->name }}</strong><br>
                                                <small class="text-muted">{{ $app->user->email }}</small>
                                            </td>
                                            <td>{{ $app->journal->title }}</td>
                                            <td>{{ $app->affiliation }}</td>
                                            <td>{{ $app->primary_research_area }}</td>
                                            <td>
                                                @if($app->status === 'pending')
                                                    <span class="badge bg-warning text-dark">Pending</span>
                                                @elseif($app->status === 'accepted')
                                                    <span class="badge bg-success">Accepted</span>
                                                @elseif($app->status === 'denied')
                                                    <span class="badge bg-danger">Denied</span>
                                                @endif
                                            </td>
                                            <td>{{ $app->created_at->format('M d, Y') }}</td>
                                            <td>
                                                <a href="{{ route('admin.reviewer_applications.show', $app->id) }}" class="btn btn-sm btn-info text-white">View</a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center py-4">No reviewer applications found.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        
                        <div class="d-flex justify-content-end mt-3">
                            {{ $applications->appends(['status' => $currentStatus])->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
