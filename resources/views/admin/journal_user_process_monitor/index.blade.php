@extends('layouts.app')
@section('title', __('Journal User Process Monitor'))

@section('content')
<div class="app-main-outer">
    <div class="app-main-inner">
        <div class="page-title-actions px-3 d-flex justify-content-between align-items-center mb-4">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }}</a></li>
                    <li class="breadcrumb-item active" aria-current="page">{{ __('Journal User Process Monitor') }}</li>
                </ol>
            </nav>
        </div>
        
        <div class="page-header">
            <div class="page-title">
                <h4>{{ __('Journal User Process Monitor') }}</h4>
                <h6>{{ __('Monitor detailed process states for individual journal users.') }}</h6>
            </div>
        </div>

        @if($journals->isEmpty())
            <div class="alert alert-warning">
                {{ __('No journals available or you are not authorized to view any journal process flows.') }}
            </div>
        @else
            <div class="card mb-4">
                <div class="card-body">
                    <form action="{{ route('admin.journal.process-flow.users.index') }}" method="GET" class="row align-items-end g-3">
                        <div class="col-md-3">
                            <label for="journal_id" class="form-label fw-bold">{{ __('Select Journal:') }}</label>
                            <select name="journal_id" id="journal_id" class="form-select" onchange="this.form.submit()">
                                @foreach($journals as $journal)
                                    <option value="{{ $journal->id }}" {{ $selectedJournal->id == $journal->id ? 'selected' : '' }}>
                                        {{ $journal->selector_label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="search" class="form-label">{{ __('Search') }}</label>
                            <input type="text" name="search" id="search" class="form-control" value="{{ request('search') }}" placeholder="Name, Email, Institution">
                        </div>
                        <div class="col-md-3">
                            <label for="role" class="form-label">{{ __('Role') }}</label>
                            <select name="role" id="role" class="form-select">
                                <option value="">{{ __('All Roles') }}</option>
                                <option value="owner" {{ request('role') == 'owner' ? 'selected' : '' }}>Owner</option>
                                <option value="editor" {{ request('role') == 'editor' ? 'selected' : '' }}>Editor</option>
                                <option value="reviewer" {{ request('role') == 'reviewer' ? 'selected' : '' }}>Reviewer</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-primary w-100">{{ __('Filter') }}</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>{{ __('User') }}</th>
                                    <th>{{ __('Roles') }}</th>
                                    <th>{{ __('Institution') }}</th>
                                    <th>{{ __('Current Process') }}</th>
                                    <th>{{ __('Blocker') }}</th>
                                    <th>{{ __('Progress') }}</th>
                                    <th>{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($users as $u)
                                    <tr>
                                        <td>
                                            <div class="fw-bold">{{ $u->name }}</div>
                                            <div class="text-muted small">{{ $u->email }}</div>
                                        </td>
                                        <td>
                                            @foreach($u->roles as $r)
                                                <span class="badge bg-secondary">{{ ucfirst($r) }}</span>
                                            @endforeach
                                        </td>
                                        <td>{{ $u->institution }}</td>
                                        <td>
                                            <span class="badge bg-primary">{{ $u->current_process }}</span>
                                        </td>
                                        <td>
                                            <small class="text-danger">{{ $u->current_blocker }}</small>
                                        </td>
                                        <td>
                                            <div class="fw-bold">{{ $u->progress_text }}</div>
                                        </td>
                                        <td>
                                            <a href="{{ route('admin.journal.process-flow.users.show', ['journal' => $selectedJournal->id, 'user' => $u->id]) }}" class="btn btn-sm btn-outline-primary">
                                                {{ __('View Process') }}
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-4 text-muted">
                                            {{ __('No users found matching the criteria.') }}
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
