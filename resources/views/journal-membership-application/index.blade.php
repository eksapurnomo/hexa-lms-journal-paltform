@extends('layouts.app')

@section('content')
    <div class="app-main-outer">
        <div class="app-main-inner">
            <div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>My Journal Applications</h2>
        <a href="{{ route('membership-applications.create') }}" class="btn btn-primary">New Application</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="card">
        <div class="card-body">
            <table class="table">
                <thead>
                    <tr>
                        <th>Journal</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Submitted</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($applications as $app)
                    <tr>
                        <td>{{ $app->journal->title }}</td>
                        <td>{{ ucfirst($app->requested_role) }}</td>
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
                            <a href="{{ route('membership-applications.show', $app->id) }}" class="btn btn-sm btn-info">View / Update</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center">No applications found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        </div>
    </div>
            </div>
        </div>
    </div>
@endsection
