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
        <a href="{{ route('subcontractors.create') }}" class="btn btn-primary btn-custom">
            <i class="fas fa-plus me-2"></i>New Subcontractor
        </a>
    </div>
</div>

<div class="table-card">
    <div class="table-responsive">
        <table class="table table-hover datatable">
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
                @forelse($subcontractors as $subcontractor)
                <tr>
                    <td>
                        <a href="{{ route('subcontractors.show', $subcontractor) }}" class="fw-bold">
                            {{ $subcontractor->name }}
                        </a>
                    </td>
                    <td>{{ $subcontractor->contact_person ?? 'N/A' }}</td>
                    <td>{{ $subcontractor->email ?? 'N/A' }}</td>
                    <td>{{ $subcontractor->phone ?? 'N/A' }}</td>
                    <td>{{ $subcontractor->projects->count() }}</td>
                    <td>
                        <span class="badge {{ $subcontractor->is_active ? 'bg-success' : 'bg-danger' }}">
                            {{ $subcontractor->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                    <td>
                        <a href="{{ route('subcontractors.show', $subcontractor) }}" class="btn btn-sm btn-info">
                            <i class="fas fa-eye"></i>
                        </a>
                        <a href="{{ route('subcontractors.edit', $subcontractor) }}" class="btn btn-sm btn-warning">
                            <i class="fas fa-edit"></i>
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center py-4">
                        <i class="fas fa-users fa-3x text-muted mb-3"></i>
                        <h4>No Subcontractors Found</h4>
                        <a href="{{ route('subcontractors.create') }}" class="btn btn-primary">Add Subcontractor</a>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $subcontractors->links() }}
</div>
@endsection
