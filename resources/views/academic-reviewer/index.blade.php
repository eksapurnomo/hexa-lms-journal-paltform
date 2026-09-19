@extends('layouts.app')

@section('title', $app_setting['name'] . ' | ' . __('Academic & Reviewer Management'))

@section('content')
    <!-- ****Body-Section***** -->
    <div class="app-main-outer">
        <div class="app-main-inner">
            <div class="page-title-actions px-3 py-3 d-flex justify-content-between align-items-center bg-white rounded mb-3">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb m-0 p-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }}</a></li>
                        <li class="breadcrumb-item active" aria-current="page">{{ __('Academic & Reviewer Management') }}</li>
                    </ol>
                </nav>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="card mb-5">
                        <div class="card-body">
                            <form action="{{ route('academic-reviewers.index') }}" method="GET" class="mb-4">
                                <div class="row g-3 align-items-center">
                                    <div class="col-md-4">
                                        <div class="input-group">
                                            <input type="text" class="form-control" name="search" placeholder="{{ __('Search by name or email') }}" value="{{ request('search') }}">
                                            <button class="btn btn-outline-primary px-3" type="submit"><i class="bi bi-search"></i></button>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <select name="journal_id" class="form-select" onchange="this.form.submit()">
                                            <option value="">{{ __('All Journals') }}</option>
                                            @foreach($journals as $journal)
                                                <option value="{{ $journal->id }}" {{ request('journal_id') == $journal->id ? 'selected' : '' }}>{{ $journal->title }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <select name="role" class="form-select" onchange="this.form.submit()">
                                            <option value="">{{ __('All Roles') }}</option>
                                            <option value="owner" {{ request('role') == 'owner' ? 'selected' : '' }}>{{ __('Owner') }}</option>
                                            <option value="editor" {{ request('role') == 'editor' ? 'selected' : '' }}>{{ __('Editor') }}</option>
                                            <option value="reviewer" {{ request('role') == 'reviewer' ? 'selected' : '' }}>{{ __('Reviewer') }}</option>
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <a href="{{ route('academic-reviewers.index') }}" class="btn btn-link text-decoration-none"><i class="bi bi-arrow-counterclockwise"></i> {{ __('Reset') }}</a>
                                    </div>
                                </div>
                            </form>

                            <div class="table-responsive">
                                <table class="table table-striped table-hover align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th>{{ __('ID') }}</th>
                                            <th>{{ __('Name') }}</th>
                                            <th>{{ __('Email') }}</th>
                                            <th>{{ __('Highest Degree') }}</th>
                                            <th>{{ __('Memberships') }}</th>
                                            <th class="text-end">{{ __('Action') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($users as $user)
                                            <tr>
                                                <td>{{ $user->id }}</td>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <img src="{{ $user->profilePicturePath }}" alt="{{ $user->name }}" class="rounded-circle me-2" width="40" height="40" style="object-fit: cover;">
                                                        <strong>{{ $user->name }}</strong>
                                                    </div>
                                                </td>
                                                <td>{{ $user->email }}</td>
                                                <td>
                                                    @if($user->academicProfile)
                                                        {{ $user->academicProfile->highest_degree ?? '-' }}
                                                    @else
                                                        <span class="text-muted fst-italic">{{ __('No Profile') }}</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($user->journalMemberships && $user->journalMemberships->count() > 0)
                                                        <span class="badge bg-primary rounded-pill">{{ $user->journalMemberships->count() }}</span>
                                                    @else
                                                        <span class="text-muted">-</span>
                                                    @endif
                                                </td>
                                                <td class="text-end">
                                                    <a href="{{ route('academic-reviewers.show', $user->id) }}" class="btn btn-sm btn-outline-info" title="{{ __('View Details') }}">
                                                        <i class="bi bi-eye"></i> {{ __('View') }}
                                                    </a>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6" class="text-center text-muted py-4">{{ __('No academic profiles or journal memberships found.') }}</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            
                            @if($users->hasPages())
                                <div class="d-flex justify-content-center mt-4">
                                    {{ $users->links() }}
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
