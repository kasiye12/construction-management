@extends('layouts.app')

@section('title', 'Subcontractors - CMS')

@section('content')
<div class="page-header">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h2>👥 Subcontractors</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active">Subcontractors</li>
                </ol>
            </nav>
        </div>
        <a href="{{ route('subcontractors.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i> New Subcontractor
        </a>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>Name</th>
                    <th>Contact Person</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Projects</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($subcontractors as $sub)
                <tr>
                    <td><a href="{{ route('subcontractors.show', $sub) }}" class="fw-bold text-decoration-none">{{ $sub->name }}</a></td>
                    <td>{{ $sub->contact_person ?? '-' }}</td>
                    <td>{{ $sub->email ?? '-' }}</td>
                    <td>{{ $sub->phone ?? '-' }}</td>
                    <td><span class="badge bg-info">{{ $sub->projects_count }}</span></td>
                    <td>
                        <span class="badge {{ $sub->is_active ? 'bg-success' : 'bg-secondary' }}">
                            {{ $sub->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                    <td>
                        <div class="btn-group btn-group-sm">
                            <a href="{{ route('subcontractors.show', $sub) }}" class="btn btn-info"><i class="fas fa-eye"></i></a>
                            <a href="{{ route('subcontractors.edit', $sub) }}" class="btn btn-warning"><i class="fas fa-edit"></i></a>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="text-center py-4">No subcontractors found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer">{{ $subcontractors->links() }}</div>
</div>
@endsection
