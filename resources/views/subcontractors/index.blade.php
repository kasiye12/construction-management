@extends('layouts.app')

@section('title', 'Subcontractors - CMS')

@section('content')
<div class="page-header">
    <div class="d-flex justify-content-between align-items-center">
        <div><h2>👥 Subcontractors</h2></div>
        @if(\App\Helpers\PermissionHelper::canCreate('subcontractors'))
        <a href="{{ route('subcontractors.create') }}" class="btn btn-primary"><i class="fas fa-plus me-1"></i> New Subcontractor</a>
        @endif
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr><th>Name</th><th>Contact</th><th>Email</th><th>Phone</th><th>Status</th><th>Actions</th></tr>
            </thead>
            <tbody>
                @forelse($subcontractors as $sub)
                <tr>
                    <td><a href="{{ route('subcontractors.show', $sub) }}" class="fw-bold text-decoration-none">{{ $sub->name }}</a></td>
                    <td>{{ $sub->contact_person ?? '-' }}</td>
                    <td>{{ $sub->email ?? '-' }}</td>
                    <td>{{ $sub->phone ?? '-' }}</td>
                    <td><span class="badge {{ $sub->is_active ? 'bg-success' : 'bg-secondary' }}">{{ $sub->is_active ? 'Active' : 'Inactive' }}</span></td>
                    <td>
                        <div class="btn-group btn-group-sm">
                            @if(\App\Helpers\PermissionHelper::canView('subcontractors'))
                            <a href="{{ route('subcontractors.show', $sub) }}" class="btn btn-info"><i class="fas fa-eye"></i></a>
                            @endif
                            @if(\App\Helpers\PermissionHelper::canEdit('subcontractors'))
                            <a href="{{ route('subcontractors.edit', $sub) }}" class="btn btn-warning"><i class="fas fa-edit"></i></a>
                            @endif
                            @if(\App\Helpers\PermissionHelper::canDelete('subcontractors'))
                            <form action="{{ route('subcontractors.destroy', $sub) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-danger"><i class="fas fa-trash"></i></button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center py-4">No subcontractors found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer">{{ $subcontractors->links() }}</div>
</div>
@endsection
