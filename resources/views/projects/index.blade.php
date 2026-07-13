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
        <a href="{{ route('projects.create') }}" class="btn btn-primary btn-custom">
            <i class="fas fa-plus me-2"></i>New Project
        </a>
    </div>
</div>

<div class="table-card">
    <div class="table-responsive">
        <table class="table table-hover datatable">
            <thead class="table-light">
                <tr>
                    <th>ID</th>
                    <th>Project Name</th>
                    <th>Client</th>
                    <th>Contractor</th>
                    <th>Contract Amount</th>
                    <th>Status</th>
                    <th>Start Date</th>
                    <th>End Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($projects as $project)
                <tr>
                    <td>{{ $project->id }}</td>
                    <td>
                        <a href="{{ route('projects.show', $project) }}" class="text-decoration-none fw-bold">
                            {{ $project->name }}
                        </a>
                    </td>
                    <td>{{ $project->client_name ?? 'N/A' }}</td>
                    <td>{{ $project->contractor_name ?? 'N/A' }}</td>
                    <td>{{ number_format($project->contract_amount, 2) }} ETB</td>
                    <td>
                        @if($project->status == 'active')
                            <span class="badge badge-status bg-success">Active</span>
                        @elseif($project->status == 'completed')
                            <span class="badge badge-status bg-primary">Completed</span>
                        @elseif($project->status == 'on_hold')
                            <span class="badge badge-status bg-warning">On Hold</span>
                        @else
                            <span class="badge badge-status bg-danger">Cancelled</span>
                        @endif
                    </td>
                    <td>{{ $project->start_date ? $project->start_date->format('M d, Y') : 'N/A' }}</td>
                    <td>{{ $project->end_date ? $project->end_date->format('M d, Y') : 'N/A' }}</td>
                    <td>
                        <div class="btn-group">
                            <a href="{{ route('projects.show', $project) }}" class="btn btn-sm btn-info" title="View">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('projects.edit', $project) }}" class="btn btn-sm btn-warning" title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="{{ route('projects.destroy', $project) }}" method="POST" class="d-inline" 
                                  onsubmit="return confirm('Are you sure you want to delete this project?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger" title="Delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="text-center py-5">
                        <i class="fas fa-building fa-4x text-muted mb-3"></i>
                        <h4>No Projects Found</h4>
                        <p class="text-muted">Start by creating your first construction project.</p>
                        <a href="{{ route('projects.create') }}" class="btn btn-primary btn-custom">
                            <i class="fas fa-plus me-2"></i>Create Project
                        </a>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-3">
        {{ $projects->links() }}
    </div>
</div>
@endsection
