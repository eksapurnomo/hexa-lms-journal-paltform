@extends('layouts.app')

@section('content')
    <div class="app-main-outer">
        <div class="app-main-inner">
            <div class="container-fluid py-4">
    <h2>Membership Verification Queue</h2>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="card mb-4">
        <div class="card-body">
            <form action="{{ route('admin.membership-verifications.index') }}" method="GET" class="row">
                <div class="col-md-3">
                    <select name="status" class="form-control">
                        <option value="">All Statuses</option>
                        <option value="submitted" @if(request('status') == 'submitted') selected @endif>Submitted</option>
                        <option value="under_review" @if(request('status') == 'under_review') selected @endif>Under Review</option>
                        <option value="needs_revision" @if(request('status') == 'needs_revision') selected @endif>Needs Revision</option>
                        <option value="approved" @if(request('status') == 'approved') selected @endif>Approved</option>
                        <option value="rejected" @if(request('status') == 'rejected') selected @endif>Rejected</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <select name="journal_id" class="form-control">
                        <option value="">All Journals</option>
                        @foreach($journals as $journal)
                            <option value="{{ $journal->id }}" @if(request('journal_id') == $journal->id) selected @endif>{{ $journal->title }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">Filter</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <table class="table">
                <thead>
                    <tr>
                        <th>Applicant</th>
                        <th>Journal</th>
                        <th>Role</th>
                        <th>Inst. Email</th>
                        <th>Status</th>
                        <th>Submitted</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($applications as $app)
                    <tr>
                        <td>{{ $app->user->name }}</td>
                        <td>{{ $app->journal->title }}</td>
                        <td>{{ ucfirst($app->requested_role) }}</td>
                        <td>
                            @if($app->user->academicProfile && $app->user->academicProfile->institutional_email)
                                <span class="badge bg-info">Provided</span>
                            @else
                                <span class="badge bg-secondary">None</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge 
                                @if($app->status == 'approved') bg-success 
                                @elseif($app->status == 'rejected') bg-danger 
                                @elseif($app->status == 'needs_revision') bg-warning
                                @else bg-secondary @endif">
                                {{ ucfirst(str_replace('_', ' ', $app->status)) }}
                            </span>
                        </td>
                        <td>{{ $app->submitted_at ? $app->submitted_at->format('M d, Y') : '-' }}</td>
                        <td>
                            <a href="{{ route('admin.membership-verifications.show', $app->id) }}" class="btn btn-sm btn-info">Review</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center">No applications found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
            
            {{ $applications->links() }}
        </div>
        </div>
    </div>
            </div>
        </div>
    </div>
@endsection
