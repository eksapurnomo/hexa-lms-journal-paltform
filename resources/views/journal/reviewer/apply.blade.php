@extends('layouts.app')

@section('title', __('Apply as Reviewer'))

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">Reviewer Application</h4>
                </div>
                <div class="card-body">
                    @if(session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif

                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('reviewer.store') }}" method="POST">
                        @csrf
                        
                        <h5 class="border-bottom pb-2 mb-3">1. Select Journal</h5>
                        <div class="mb-4">
                            <label for="journal_id" class="form-label fw-bold">Journal <span class="text-danger">*</span></label>
                            <select name="journal_id" id="journal_id" class="form-select" required>
                                <option value="">-- Select a Journal --</option>
                                @foreach($journals as $journal)
                                    <option value="{{ $journal->id }}" {{ old('journal_id') == $journal->id ? 'selected' : '' }}>{{ $journal->title }}</option>
                                @endforeach
                            </select>
                        </div>

                        <h5 class="border-bottom pb-2 mb-3">2. Personal & Academic Information</h5>
                        <div class="row mb-4">
                            <div class="col-md-6 mb-3">
                                <label for="name" class="form-label fw-bold">Full Name</label>
                                <input type="text" class="form-control bg-light" value="{{ $user->name }}" readonly disabled>
                                <small class="text-muted">Extracted from your account profile.</small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label fw-bold">Email</label>
                                <input type="email" class="form-control bg-light" value="{{ $user->email }}" readonly disabled>
                            </div>
                            <div class="col-md-12 mb-3">
                                <label for="affiliation" class="form-label fw-bold">Affiliation / Institution <span class="text-danger">*</span></label>
                                <input type="text" name="affiliation" id="affiliation" class="form-control" value="{{ old('affiliation') }}" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="department" class="form-label fw-bold">Department <span class="text-danger">*</span></label>
                                <input type="text" name="department" id="department" class="form-control" value="{{ old('department') }}" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="academic_position" class="form-label fw-bold">Academic Position <span class="text-danger">*</span></label>
                                <input type="text" name="academic_position" id="academic_position" class="form-control" value="{{ old('academic_position') }}" placeholder="e.g. Professor, Postdoc, Researcher" required>
                            </div>
                        </div>

                        <h5 class="border-bottom pb-2 mb-3">3. Academic Profile</h5>
                        <div class="row mb-4">
                            <div class="col-md-6 mb-3">
                                <label for="orcid" class="form-label fw-bold">ORCID (Optional)</label>
                                <input type="text" name="orcid" id="orcid" class="form-control" value="{{ old('orcid') }}" placeholder="0000-0000-0000-0000">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="academic_url" class="form-label fw-bold">Academic / Research URL (Optional)</label>
                                <input type="url" name="academic_url" id="academic_url" class="form-control" value="{{ old('academic_url') }}" placeholder="https://...">
                            </div>
                        </div>

                        <h5 class="border-bottom pb-2 mb-3">4. Expertise</h5>
                        <div class="row mb-4">
                            <div class="col-md-12 mb-3">
                                <label for="primary_research_area" class="form-label fw-bold">Primary Research Area <span class="text-danger">*</span></label>
                                <input type="text" name="primary_research_area" id="primary_research_area" class="form-control" value="{{ old('primary_research_area') }}" required>
                            </div>
                            <div class="col-md-12 mb-3">
                                <label for="research_keywords" class="form-label fw-bold">Research Keywords</label>
                                <input type="text" name="research_keywords" id="research_keywords" class="form-control" value="{{ old('research_keywords') }}" placeholder="Separate with commas">
                            </div>
                            <div class="col-md-12 mb-3">
                                <label for="expertise" class="form-label fw-bold">Detailed Areas of Expertise</label>
                                <textarea name="expertise" id="expertise" rows="3" class="form-control">{{ old('expertise') }}</textarea>
                            </div>
                        </div>

                        <h5 class="border-bottom pb-2 mb-3">5. Experience & Availability</h5>
                        <div class="row mb-4">
                            <div class="col-md-6 mb-3">
                                <label for="years_of_experience" class="form-label fw-bold">Years of Reviewing Experience <span class="text-danger">*</span></label>
                                <input type="number" name="years_of_experience" id="years_of_experience" class="form-control" min="0" max="100" value="{{ old('years_of_experience', 0) }}" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="max_reviews_per_month" class="form-label fw-bold">Maximum Reviews per Month <span class="text-danger">*</span></label>
                                <input type="number" name="max_reviews_per_month" id="max_reviews_per_month" class="form-control" min="1" max="50" value="{{ old('max_reviews_per_month', 1) }}" required>
                            </div>
                            <div class="col-md-12 mb-3">
                                <label for="previous_experience" class="form-label fw-bold">Previous Reviewing Experience (Optional)</label>
                                <textarea name="previous_experience" id="previous_experience" rows="3" class="form-control" placeholder="List journals you have reviewed for, if any...">{{ old('previous_experience') }}</textarea>
                            </div>
                            <div class="col-md-12 mb-3">
                                <div class="form-check">
                                    <input type="checkbox" name="available_for_review" id="available_for_review" class="form-check-input" value="1" {{ old('available_for_review') || old('available_for_review') === null ? 'checked' : '' }}>
                                    <label class="form-check-label fw-bold" for="available_for_review">I am currently available to receive review invitations.</label>
                                </div>
                            </div>
                        </div>

                        <h5 class="border-bottom pb-2 mb-3">6. Agreements</h5>
                        <div class="mb-4">
                            <div class="form-check mb-2">
                                <input type="checkbox" name="agreed_confidentiality" id="agreed_confidentiality" class="form-check-input" value="1" required {{ old('agreed_confidentiality') ? 'checked' : '' }}>
                                <label class="form-check-label" for="agreed_confidentiality">I agree to maintain reviewer confidentiality. <span class="text-danger">*</span></label>
                            </div>
                            <div class="form-check mb-2">
                                <input type="checkbox" name="agreed_conflict_of_interest" id="agreed_conflict_of_interest" class="form-check-input" value="1" required {{ old('agreed_conflict_of_interest') ? 'checked' : '' }}>
                                <label class="form-check-label" for="agreed_conflict_of_interest">I agree to declare any conflicts of interest before accepting a review assignment. <span class="text-danger">*</span></label>
                            </div>
                            <div class="form-check mb-2">
                                <input type="checkbox" name="agreed_guidelines" id="agreed_guidelines" class="form-check-input" value="1" required {{ old('agreed_guidelines') ? 'checked' : '' }}>
                                <label class="form-check-label" for="agreed_guidelines">I agree to follow the journal's reviewer guidelines. <span class="text-danger">*</span></label>
                            </div>
                        </div>

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <button type="submit" class="btn btn-primary px-4">Submit Application</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
