@extends('layouts.app')

@section('title', 'Projects - CMS')

@section('content')
<div class="page-header">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h2>🏗️ Projects</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active">Projects</li>
                </ol>
            </nav>
        </div>
        @if(\App\Helpers\PermissionHelper::canCreate('projects'))
        <a href="{{ route('projects.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i> New Project
        </a>
        @endif
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr><th>Project</th><th>Client</th><th>Status</th><th>Amount</th><th>Actions</th></tr>
            </thead>
            <tbody>
                @forelse($projects as $project)
                <tr>
                    <td><a href="{{ route('projects.show', $project) }}" class="fw-bold text-decoration-none">{{ Str::limit($project->name, 40) }}</a></td>
                    <td>{{ $project->client_name ?? 'N/A' }}</td>
                    <td><span class="badge bg-{{ $project->status=='active'?'success':'secondary' }}">{{ ucfirst($project->status) }}</span></td>
                    <td>{{ number_format($project->contract_amount, 0) }} ETB</td>
                    <td>
                        <div class="btn-group btn-group-sm">
                            @if(\App\Helpers\PermissionHelper::canView('projects'))
                            <a href="{{ route('projects.show', $project) }}" class="btn btn-info" title="View"><i class="fas fa-eye"></i></a>
                            @endif
                            @if(\App\Helpers\PermissionHelper::canEdit('projects'))
                            <a href="{{ route('projects.edit', $project) }}" class="btn btn-warning" title="Edit"><i class="fas fa-edit"></i></a>
                            @endif
                            @if(\App\Helpers\PermissionHelper::canDelete('projects'))
                            <form action="{{ route('projects.destroy', $project) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-danger" title="Delete"><i class="fas fa-trash"></i></button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" class="text-center py-4">No projects found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer">{{ $projects->links() }}</div>
</div>
@endsection
