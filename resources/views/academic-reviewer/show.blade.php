@extends('layouts.app')

@section('title', $app_setting['name'] . ' | ' . __('User Detail'))

@section('content')
    <div class="app-main-outer">
        <div class="app-main-inner">
            <div class="page-title-actions px-3 py-3 d-flex justify-content-between align-items-center bg-white rounded mb-3">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb m-0 p-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }}</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('academic-reviewers.index') }}">{{ __('Academic & Reviewer Management') }}</a></li>
                        <li class="breadcrumb-item active" aria-current="page">{{ __('Detail') }}</li>
                    </ol>
                </nav>
            </div>

            <div class="row">
                <div class="col-md-4">
                    <div class="card mb-4">
                        <div class="card-body text-center">
                            <img src="{{ $user->profilePicturePath }}" alt="{{ $user->name }}" class="rounded-circle mb-3" width="120" height="120" style="object-fit: cover;">
                            <h4 class="card-title mb-1">{{ $user->name }}</h4>
                            <p class="text-muted">{{ $user->email }}</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-8">
                    <div class="card mb-4">
                        <div class="card-header border-bottom">
                            <h5 class="m-0">{{ __('Academic Profile') }}</h5>
                        </div>
                        <div class="card-body">
                            @if($user->academicProfile)
                                <table class="table table-bordered mb-0">
                                    <tr>
                                        <th width="35%">{{ __('Highest Degree') }}</th>
                                        <td>{{ $user->academicProfile->highest_degree ?? '-' }}</td>
                                    </tr>
                                    <tr>
                                        <th>{{ __('Academic Position') }}</th>
                                        <td>{{ $user->academicProfile->academic_position ?? '-' }}</td>
                                    </tr>
                                    <tr>
                                        <th>{{ __('Institution') }}</th>
                                        <td>{{ $user->academicProfile->institution ?? '-' }}</td>
                                    </tr>
                                    <tr>
                                        <th>{{ __('Department') }}</th>
                                        <td>{{ $user->academicProfile->department ?? '-' }}</td>
                                    </tr>
                                    <tr>
                                        <th>{{ __('Country') }}</th>
                                        <td>{{ $user->academicProfile->country ?? '-' }}</td>
                                    </tr>
                                    <tr>
                                        <th>{{ __('Research Interests') }}</th>
                                        <td>{{ $user->academicProfile->research_interests ?? '-' }}</td>
                                    </tr>
                                    <tr>
                                        <th>{{ __('Expertise') }}</th>
                                        <td>{{ $user->academicProfile->expertise ?? '-' }}</td>
                                    </tr>
                                    <tr>
                                        <th>{{ __('ORCID') }}</th>
                                        <td>
                                            @if($user->academicProfile->orcid)
                                                <a href="https://orcid.org/{{ $user->academicProfile->orcid }}" target="_blank">{{ $user->academicProfile->orcid }}</a>
                                            @else
                                                -
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>{{ __('Scopus Author ID') }}</th>
                                        <td>{{ $user->academicProfile->scopus_id ?? '-' }}</td>
                                    </tr>
                                    <tr>
                                        <th>{{ __('Google Scholar URL') }}</th>
                                        <td>
                                            @if($user->academicProfile->google_scholar_url)
                                                <a href="{{ $user->academicProfile->google_scholar_url }}" target="_blank">{{ __('View Profile') }}</a>
                                            @else
                                                -
                                            @endif
                                        </td>
                                    </tr>
                                </table>
                                
                                @if($user->academicProfile->biography)
                                    <div class="mt-4">
                                        <h6><strong>{{ __('Biography') }}</strong></h6>
                                        <div class="p-3 bg-light rounded text-muted">
                                            {{ $user->academicProfile->biography }}
                                        </div>
                                    </div>
                                @endif
                            @else
                                <p class="text-muted fst-italic mb-0">{{ __('This user does not have an academic profile.') }}</p>
                            @endif
                        </div>
                    </div>

                    <div class="card mb-4">
                        <div class="card-header border-bottom">
                            <h5 class="m-0">{{ __('Journal Memberships') }}</h5>
                        </div>
                        <div class="card-body">
                            @if($user->journalMemberships && $user->journalMemberships->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-striped align-middle mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th>{{ __('Journal') }}</th>
                                                <th>{{ __('Role') }}</th>
                                                <th>{{ __('Status') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($user->journalMemberships as $membership)
                                            <tr>
                                                <td><strong>{{ $membership->journal->title ?? 'Unknown Journal' }}</strong></td>
                                                <td><span class="badge bg-secondary text-capitalize">{{ $membership->role }}</span></td>
                                                <td>
                                                    @if($membership->status == 'active')
                                                        <span class="badge bg-success">{{ __('Active') }}</span>
                                                    @else
                                                        <span class="badge bg-warning">{{ ucfirst($membership->status) }}</span>
                                                    @endif
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <p class="text-muted fst-italic mb-0">{{ __('This user is not a member of any journals.') }}</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
