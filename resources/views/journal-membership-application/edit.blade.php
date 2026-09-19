@extends('layouts.app')

@section('content')
    <div class="app-main-outer">
        <div class="app-main-inner">
            <div class="container-fluid py-4">
    <h2>Edit Application (Needs Revision)</h2>

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('membership-applications.update', $application->id) }}" method="POST">
        @csrf
        @method('PUT')
        
        <div class="card mb-4">
            <div class="card-header">
                <h5>1. Journal Application (Locked)</h5>
            </div>
            <div class="card-body row">
                <div class="col-md-6 mb-3">
                    <label>Journal</label>
                    <input type="text" class="form-control" value="{{ $application->journal->title }}" readonly disabled>
                </div>
                <div class="col-md-6 mb-3">
                    <label>Requested Role</label>
                    <input type="text" class="form-control" value="{{ ucfirst($application->requested_role) }}" readonly disabled>
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header">
                <h5>2. Academic & Researcher Type</h5>
            </div>
            <div class="card-body row">
                <div class="col-md-12 mb-3">
                    <label>Academic Type</label>
                    <select name="academic_type" class="form-control" required>
                        <option value="">Select Type...</option>
                        <option value="lecturer" {{ old('academic_type', $profile->academic_type) == 'lecturer' ? 'selected' : '' }}>Lecturer</option>
                        <option value="researcher" {{ old('academic_type', $profile->academic_type) == 'researcher' ? 'selected' : '' }}>Researcher</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header">
                <h5>3. Identity</h5>
            </div>
            <div class="card-body row">
                <div class="col-md-6 mb-3">
                    <label>Full Name</label>
                    <input type="text" class="form-control" value="{{ Auth::user()->name }}" readonly disabled>
                </div>
                <div class="col-md-6 mb-3">
                    <label>Country</label>
                    <input type="text" name="country" class="form-control" value="{{ old('country', $profile->country) }}">
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header">
                <h5>4. Organization</h5>
            </div>
            <div class="card-body row">
                <div class="col-md-12 mb-3">
                    <label>Institution Type</label>
                    <select name="institution_type" id="institution_type" class="form-control">
                        <option value="">Select Type...</option>
                        <option value="university" {{ old('institution_type', $profile->institution_type) == 'university' ? 'selected' : '' }}>University</option>
                        <option value="research_institute" {{ old('institution_type', $profile->institution_type) == 'research_institute' ? 'selected' : '' }}>Research Institute</option>
                        <option value="government_research" {{ old('institution_type', $profile->institution_type) == 'government_research' ? 'selected' : '' }}>Government Research Institution</option>
                        <option value="ngo" {{ old('institution_type', $profile->institution_type) == 'ngo' ? 'selected' : '' }}>NGO</option>
                        <option value="private_research" {{ old('institution_type', $profile->institution_type) == 'private_research' ? 'selected' : '' }}>Private Research Organization</option>
                        <option value="think_tank" {{ old('institution_type', $profile->institution_type) == 'think_tank' ? 'selected' : '' }}>Think Tank</option>
                        <option value="other" {{ old('institution_type', $profile->institution_type) == 'other' ? 'selected' : '' }}>Other</option>
                        <option value="independent" {{ old('institution_type', $profile->institution_type) == 'independent' ? 'selected' : '' }}>Independent</option>
                    </select>
                </div>

                <div class="col-md-6 mb-3 org-field">
                    <label>Institution (Normalized)</label>
                    <select name="institution_id" id="institution_id" class="form-control">
                        <option value="">Select Institution...</option>
                        @foreach($institutions as $inst)
                            <option value="{{ $inst->id }}" @if(old('institution_id', $profile->institution_id) == $inst->id) selected @endif>{{ $inst->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6 mb-3 org-field">
                    <label>Institution (Text)</label>
                    <input type="text" name="institution" id="institution" class="form-control" value="{{ old('institution', $profile->institution) }}">
                </div>
                <div class="col-md-12 mb-3 org-field">
                    <label>Department / Unit</label>
                    <input type="text" name="department" id="department" class="form-control" value="{{ old('department', $profile->department) }}">
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header">
                <h5>5. Professional</h5>
            </div>
            <div class="card-body row">
                <div class="col-md-6 mb-3">
                    <label>Highest Degree</label>
                    <input type="text" name="highest_degree" class="form-control" value="{{ old('highest_degree', $profile->highest_degree) }}" placeholder="e.g. PhD, Master">
                </div>
                <div class="col-md-6 mb-3">
                    <label>Academic Position</label>
                    <input type="text" name="academic_position" class="form-control" value="{{ old('academic_position', $profile->academic_position) }}" placeholder="e.g. Professor, Research Fellow">
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header">
                <h5>6. Research</h5>
            </div>
            <div class="card-body row">
                <div class="col-md-12 mb-3">
                    <label>Biography</label>
                    <textarea name="biography" class="form-control" rows="3">{{ old('biography', $profile->biography) }}</textarea>
                </div>
                <div class="col-md-12 mb-3">
                    <label>Research Interests</label>
                    <textarea name="research_interests" class="form-control" rows="2">{{ old('research_interests', $profile->research_interests) }}</textarea>
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header">
                <h5>7. Researcher Identity <small class="text-muted">(Optional)</small></h5>
            </div>
            <div class="card-body row">
                <div class="col-md-6 mb-3">
                    <label>ORCID</label>
                    <input type="text" name="orcid" class="form-control" value="{{ old('orcid', $profile->orcid) }}">
                </div>
                <div class="col-md-6 mb-3">
                    <label>Scopus Author ID</label>
                    <input type="text" name="scopus_author_id" class="form-control" value="{{ old('scopus_author_id', $profile->scopus_author_id) }}">
                </div>
                <div class="col-md-6 mb-3">
                    <label>SINTA ID</label>
                    <input type="text" name="sinta_id" class="form-control" value="{{ old('sinta_id', $profile->sinta_id) }}">
                </div>
                <div class="col-md-6 mb-3">
                    <label>Google Scholar URL</label>
                    <input type="url" name="google_scholar_url" class="form-control" value="{{ old('google_scholar_url', $profile->google_scholar_url) }}">
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header">
                <h5>8. Contact</h5>
            </div>
            <div class="card-body row">
                <div class="col-md-6 mb-3">
                    <label>Account Email</label>
                    <input type="email" class="form-control" value="{{ Auth::user()->email }}" readonly disabled>
                </div>
                <div class="col-md-6 mb-3">
                    <label>Institutional Email <small class="text-muted">(Optional)</small></label>
                    <input type="email" name="institutional_email" class="form-control" value="{{ old('institutional_email', $profile->institutional_email) }}">
                    <small class="form-text text-muted">Institutional email is recommended because it can help verify your academic affiliation and may speed up the verification process.</small>
                </div>
            </div>
        </div>

        <button type="submit" class="btn btn-warning">Update Profile</button>
        <a href="{{ route('membership-applications.show', $application->id) }}" class="btn btn-secondary">Cancel</a>
    </form>
            </div>
        </div>
    </div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const orgTypeSelect = document.getElementById('institution_type');
    const orgFields = document.querySelectorAll('.org-field');
    const instId = document.getElementById('institution_id');
    const instText = document.getElementById('institution');
    const deptText = document.getElementById('department');

    function toggleOrgFields() {
        if (orgTypeSelect.value === 'independent') {
            orgFields.forEach(f => f.style.display = 'none');
            instId.value = '';
            instText.value = '';
            deptText.value = '';
        } else {
            orgFields.forEach(f => f.style.display = 'block');
        }
    }

    if (orgTypeSelect) {
        orgTypeSelect.addEventListener('change', toggleOrgFields);
        toggleOrgFields();
    }
});
</script>
@endpush
@endsection
