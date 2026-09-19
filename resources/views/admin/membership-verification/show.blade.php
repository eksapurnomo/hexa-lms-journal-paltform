@extends('layouts.app')

@section('content')
    <div class="app-main-outer">
        <div class="app-main-inner">
            <div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Verify Application: {{ $application->user->name }}</h2>
        <a href="{{ route('admin.membership-verifications.index') }}" class="btn btn-secondary">Back to Queue</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="row">
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h5>Application Details</h5>
                </div>
                <div class="card-body">
                    <p><strong>Journal:</strong> {{ $application->journal->title }}</p>
                    <p><strong>Requested Role:</strong> {{ ucfirst($application->requested_role) }}</p>
                    <p><strong>Status:</strong> 
                        <span class="badge bg-secondary">{{ ucfirst(str_replace('_', ' ', $application->status)) }}</span>
                    </p>
                    <p><strong>Submitted At:</strong> {{ $application->submitted_at ? $application->submitted_at->format('M d, Y H:i') : '-' }}</p>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header">
                    <h5>Academic Profile & Identity</h5>
                </div>
                <div class="card-body row">
                    @php $profile = $application->user->academicProfile; @endphp
                    @if($profile)
                        <div class="col-md-6"><p><strong>Highest Degree:</strong> {{ $profile->highest_degree ?? '-' }}</p></div>
                        <div class="col-md-6"><p><strong>Academic Position:</strong> {{ $profile->academic_position ?? '-' }}</p></div>
                        <div class="col-md-6"><p><strong>Institution (Text):</strong> {{ $profile->institution ?? '-' }}</p></div>
                        <div class="col-md-6"><p><strong>Institution (Normalized):</strong> {{ $profile->institutionRelation ? $profile->institutionRelation->name : '-' }}</p></div>
                        <div class="col-md-6"><p><strong>Department:</strong> {{ $profile->department ?? '-' }}</p></div>
                        <div class="col-md-6"><p><strong>Country:</strong> {{ $profile->country ?? '-' }}</p></div>
                        <div class="col-md-12"><p><strong>Institutional Email:</strong> {{ $profile->institutional_email ?? '-' }}</p></div>
                        
                        <div class="col-12 mt-3"><h6>Researcher Identity</h6></div>
                        <div class="col-md-6"><p><strong>ORCID:</strong> {{ $profile->orcid ?? '-' }}</p></div>
                        <div class="col-md-6"><p><strong>SINTA ID:</strong> {{ $profile->sinta_id ?? '-' }}</p></div>
                        <div class="col-md-6"><p><strong>Scopus Author ID:</strong> {{ $profile->scopus_author_id ?? '-' }}</p></div>
                        <div class="col-md-6"><p><strong>Google Scholar:</strong> 
                            @if($profile->google_scholar_url)
                                <a href="{{ $profile->google_scholar_url }}" target="_blank">View Profile</a>
                            @else
                                -
                            @endif
                        </p></div>
                    @else
                        <div class="col-12"><p>No academic profile provided.</p></div>
                    @endif
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header">
                    <h5>Verification Evidence</h5>
                </div>
                <div class="card-body">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Category</th>
                                <th>Uploaded</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($application->verificationEvidences as $ev)
                            <tr>
                                <td>{{ ucfirst(str_replace('_', ' ', $ev->category)) }}</td>
                                <td>{{ $ev->uploaded_at->format('M d, Y') }}</td>
                                <td>
                                    <a href="{{ route('admin.verification-evidence.download', $ev->id) }}" class="btn btn-sm btn-info" target="_blank">Secure Download</a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="text-center">No evidence uploaded.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5>Verification Decision</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.membership-verifications.updateStatus', $application->id) }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label><strong>Decision</strong></label>
                            <select name="status" class="form-control" required>
                                <option value="">Select Action...</option>
                                <option value="under_review">Start / Keep Under Review</option>
                                <option value="needs_revision">Request Revision</option>
                                <option value="approved">Approve & Activate Role</option>
                                <option value="rejected">Reject</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label><strong>Reviewer Note</strong></label>
                            <textarea name="reviewer_note" class="form-control" rows="4">{{ $application->reviewer_note }}</textarea>
                            <small class="text-muted">Visible to user if revision is requested or rejected.</small>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Save Decision</button>
                    </form>
                </div>
            </div>
        </div>
        </div>
    </div>
            </div>
        </div>
    </div>
@endsection
