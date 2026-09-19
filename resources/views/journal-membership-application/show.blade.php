@extends('layouts.app')

@section('content')
    <div class="app-main-outer">
        <div class="app-main-inner">
            <div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Application Status & Evidence</h2>
        <a href="{{ route('membership-applications.index') }}" class="btn btn-secondary">Back to List</a>
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
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Application Summary</h5>
                    @if(in_array($application->status, ['draft', 'needs_revision']))
                        <a href="{{ route('membership-applications.edit', $application->id) }}" class="btn btn-sm btn-warning">Edit Profile</a>
                    @endif
                </div>
                <div class="card-body">
                    @if($application->status === 'needs_revision')
                        <div class="alert alert-warning mb-4">
                            <strong>Verifier Note:</strong> {{ $application->reviewer_note }}
                        </div>
                    @endif

                    <h6 class="text-primary border-bottom pb-2">1. Journal Application</h6>
                    <div class="row mb-3">
                        <div class="col-sm-4 text-muted">Journal</div>
                        <div class="col-sm-8 fw-bold">{{ $application->journal->title }}</div>
                        <div class="col-sm-4 text-muted">Requested Role</div>
                        <div class="col-sm-8 fw-bold">{{ ucfirst($application->requested_role) }}</div>
                    </div>

                    <h6 class="text-primary border-bottom pb-2">2. Academic & Researcher Type</h6>
                    <div class="row mb-3">
                        <div class="col-sm-4 text-muted">Academic Type</div>
                        <div class="col-sm-8">{{ ucfirst($profile->academic_type ?? 'N/A') }}</div>
                    </div>

                    <h6 class="text-primary border-bottom pb-2">3. Identity</h6>
                    <div class="row mb-3">
                        <div class="col-sm-4 text-muted">Full Name</div>
                        <div class="col-sm-8">{{ Auth::user()->name }}</div>
                        <div class="col-sm-4 text-muted">Country</div>
                        <div class="col-sm-8">{{ $profile->country ?? '-' }}</div>
                    </div>

                    <h6 class="text-primary border-bottom pb-2">4. Organization</h6>
                    <div class="row mb-3">
                        <div class="col-sm-4 text-muted">Institution Type</div>
                        <div class="col-sm-8">{{ ucwords(str_replace('_', ' ', $profile->institution_type ?? 'N/A')) }}</div>
                        <div class="col-sm-4 text-muted">Institution</div>
                        <div class="col-sm-8">
                            @if($profile->institutionRelation)
                                {{ $profile->institutionRelation->name }}
                            @else
                                {{ $profile->institution ?? '-' }}
                            @endif
                        </div>
                        <div class="col-sm-4 text-muted">Department / Unit</div>
                        <div class="col-sm-8">{{ $profile->department ?? '-' }}</div>
                    </div>

                    <h6 class="text-primary border-bottom pb-2">5. Professional</h6>
                    <div class="row mb-3">
                        <div class="col-sm-4 text-muted">Highest Degree</div>
                        <div class="col-sm-8">{{ $profile->highest_degree ?? '-' }}</div>
                        <div class="col-sm-4 text-muted">Academic Position</div>
                        <div class="col-sm-8">{{ $profile->academic_position ?? '-' }}</div>
                    </div>

                    <h6 class="text-primary border-bottom pb-2">6. Research</h6>
                    <div class="row mb-3">
                        <div class="col-sm-4 text-muted">Biography</div>
                        <div class="col-sm-8">{{ $profile->biography ?? '-' }}</div>
                        <div class="col-sm-4 text-muted">Research Interests</div>
                        <div class="col-sm-8">{{ $profile->research_interests ?? '-' }}</div>
                    </div>

                    <h6 class="text-primary border-bottom pb-2">7. Researcher Identity</h6>
                    <div class="row mb-3">
                        <div class="col-sm-4 text-muted">ORCID</div>
                        <div class="col-sm-8">{{ $profile->orcid ?? '-' }}</div>
                        <div class="col-sm-4 text-muted">Scopus Author ID</div>
                        <div class="col-sm-8">{{ $profile->scopus_author_id ?? '-' }}</div>
                        <div class="col-sm-4 text-muted">SINTA ID</div>
                        <div class="col-sm-8">{{ $profile->sinta_id ?? '-' }}</div>
                        <div class="col-sm-4 text-muted">Google Scholar</div>
                        <div class="col-sm-8">
                            @if($profile->google_scholar_url)
                                <a href="{{ $profile->google_scholar_url }}" target="_blank">View Profile</a>
                            @else
                                -
                            @endif
                        </div>
                    </div>

                    <h6 class="text-primary border-bottom pb-2">8. Contact</h6>
                    <div class="row mb-3">
                        <div class="col-sm-4 text-muted">Account Email</div>
                        <div class="col-sm-8">{{ Auth::user()->email }}</div>
                        <div class="col-sm-4 text-muted">Institutional Email</div>
                        <div class="col-sm-8">{{ $profile->institutional_email ?? '-' }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card mb-4 border-{{ $application->status == 'approved' ? 'success' : ($application->status == 'rejected' ? 'danger' : ($application->status == 'needs_revision' ? 'warning' : 'primary')) }}">
                <div class="card-header">
                    <h5>Application Status</h5>
                </div>
                <div class="card-body">
                    <p class="mb-1"><strong>Account Status:</strong> <span class="badge bg-success">Active</span></p>
                    <p class="mb-3">
                        <strong>Journal Membership Status:</strong> 
                        <span class="badge 
                            @if($application->status == 'approved') bg-success 
                            @elseif($application->status == 'rejected') bg-danger 
                            @elseif($application->status == 'needs_revision') bg-warning text-dark
                            @else bg-secondary @endif">
                            @if($application->status == 'approved')
                                Active
                            @else
                                Pending Verification ({{ ucfirst(str_replace('_', ' ', $application->status)) }})
                            @endif
                        </span>
                    </p>

                    @if($application->status == 'approved')
                        <div class="alert alert-success p-2 text-center">
                            Application approved.<br>Journal membership is active.
                        </div>
                    @endif

                    @if(in_array($application->status, ['draft', 'needs_revision']))
                        <form action="{{ route('membership-applications.submit', $application->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to submit this application for review? You will not be able to edit it while it is under review.');">
                            @csrf
                            <button type="submit" class="btn btn-primary w-100 mt-2">Submit Application</button>
                        </form>
                    @endif
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header">
                    <h5>9. Verification Evidence</h5>
                </div>
                <div class="card-body">
                    @if(in_array($application->status, ['draft', 'needs_revision']))
                        <form action="{{ route('verification-evidence.store', $application->id) }}" method="POST" enctype="multipart/form-data" class="mb-4">
                            @csrf
                            <div class="mb-2">
                                <label>Category</label>
                                <select name="category" class="form-control form-control-sm" required>
                                    <option value="identity">Identity (KTP/Passport)</option>
                                    <option value="academic_credential">Academic Credential (Diploma/Transcript)</option>
                                    <option value="institution_affiliation">Institution Affiliation (Letter)</option>
                                    <option value="researcher_identity">Researcher Identity (Screenshot/Export)</option>
                                    <option value="supporting_document">Supporting Document</option>
                                </select>
                            </div>
                            <div class="mb-2">
                                <label>Document (PDF/Image)</label>
                                <input type="file" name="document" class="form-control form-control-sm" required accept=".pdf,.jpg,.jpeg,.png,.doc,.docx">
                            </div>
                            <button type="submit" class="btn btn-sm btn-outline-primary w-100">Upload Evidence</button>
                        </form>
                        <hr>
                    @endif

                    <div class="list-group list-group-flush">
                        @forelse($application->verificationEvidences as $ev)
                            <div class="list-group-item px-0">
                                <div class="d-flex w-100 justify-content-between">
                                    <h6 class="mb-1">{{ ucfirst(str_replace('_', ' ', $ev->category)) }}</h6>
                                    <small><span class="badge bg-{{ $ev->status == 'verified' ? 'success' : ($ev->status == 'rejected' ? 'danger' : 'secondary') }}">{{ ucfirst($ev->status) }}</span></small>
                                </div>
                                <small class="text-muted">Uploaded: {{ $ev->uploaded_at->format('M d, Y') }}</small><br>
                                <a href="{{ route('verification-evidence.download', $ev->id) }}" class="btn btn-sm btn-info mt-1" target="_blank">Download</a>
                            </div>
                        @empty
                            <div class="text-center text-muted py-3">No evidence uploaded yet.</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
            </div>
        </div>
    </div>
@endsection
